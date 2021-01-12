<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Doctrine\Common\Collections\Criteria;
use Maxfonts\Reindexr\ElasticSearch\Exception\UnsupportedIndexException;
use Maxfonts\Reindexr\Input\ReindexConfig;
use Maxfonts\Reindexr\PartitionType;

/**
 * Class ReindexSettingsFactory.
 */
final class ReindexSettingsFactory implements ReindexSettingsFactoryInterface
{
    /**
     * @return \Generator<ReindexSettings>
     */
    public function generateSettings(IndexCollection $collection, ReindexConfig $config): \Generator
    {
        $collection = $this->getSortedIndexCollection($collection);

        $maxDate = $this->initMaxDate($config);
        $lastTargetIndex = '';
        $targetCollection = IndexCollection::createEmpty();
        $config = $config->withSanitizedPrefix();

        foreach ($collection as $indexName => $index) {
            // retrieve date part from index name
            $dateStr = $this->indexDateToString($config, $indexName);

            try {
                // check whether we can create a valid target index name
                $targetIndex = $this->getTargetIndexName($config, $dateStr);
            } catch (UnsupportedIndexException $ex) {
                //...otherwise continue to the next index.
                continue;
            }

            if ($this->shouldYieldSettings($lastTargetIndex, $targetIndex)) {
                // yield current collection and target index name
                yield ReindexSettings::create($targetCollection, $lastTargetIndex);
                // reset collection and start over
                $targetCollection = IndexCollection::createEmpty();
            }

            try {
                $indexDate = $this->createIndexDate($config, $dateStr);
            } catch (InvalidFormatException $ex) {
                continue;
            }

            if ($indexDate->lessThanOrEqualTo($maxDate)) {
                $targetCollection->set($indexName, $index);
                $lastTargetIndex = $targetIndex;
            }
        }

        // any leftovers? yield the rest!
        if ($targetCollection->count()) {
            yield ReindexSettings::create($targetCollection, $lastTargetIndex);
        }
    }

    private function initMaxDate(ReindexConfig $config): CarbonInterface
    {
        $maxDate = Carbon::now();
        if (false === $config->includeCurrent) {
            if ($config->to->equals(PartitionType::MONTHLY())) {
                $maxDate->subMonth();
                $maxDate->endOfMonth();
            } elseif ($config->to->equals(PartitionType::YEARLY())) {
                $maxDate->subYear();
                $maxDate->endOfYear();
            }
        }

        return $maxDate;
    }

    /**
     * @throws UnsupportedIndexException
     */
    private function createIndexDate(ReindexConfig $config, string $dateStr): CarbonInterface
    {
        $dateStr = \trim($dateStr, '-_');
        if ($config->from->equals(PartitionType::DAILY())) {
            return Carbon::createFromFormat('Y-m-d', $dateStr);
        }
        if ($config->from->equals(PartitionType::MONTHLY())) {
            return Carbon::createFromFormat('Y-m', $dateStr);
        }

        throw new UnsupportedIndexException($dateStr);
    }

    /** @noinspection NotOptimalIfConditionsInspection */
    private function getTargetIndexName(ReindexConfig $config, string $dateStr): string
    {
        $indexName = \sprintf('%s%s', $config->prefix, \mb_substr($dateStr, 0, -3));

        if ($config->to->equals(PartitionType::MONTHLY()) && !\preg_match('/\d{4}-\d{2}$/', $indexName)) {
            throw new UnsupportedIndexException($indexName);
        }

        if ($config->to->equals(PartitionType::YEARLY()) && !\preg_match('/\d{4}$/', $indexName)) {
            throw new UnsupportedIndexException($indexName);
        }

        return $indexName;
    }

    private function indexDateToString(ReindexConfig $config, string $indexName): string
    {
        return \preg_replace("/{$config->prefix}/", '', $indexName);
    }

    private function shouldYieldSettings(string $lastTargetIndex, string $targetIndex): bool
    {
        return '' !== $lastTargetIndex && $lastTargetIndex !== $targetIndex;
    }

    private function getSortedIndexCollection(IndexCollection $collection): IndexCollection
    {
        $sortByName = Criteria::create();
        $sortByName->orderBy(['name' => 'desc']);

        return $collection->matching($sortByName);
    }
}
