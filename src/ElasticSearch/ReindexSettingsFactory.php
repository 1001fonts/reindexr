<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Basster\Reindexr\Input\ReindexConfig;
use Basster\Reindexr\PartitionType;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;

/**
 * Class ReindexSettingsFactory.
 */
final class ReindexSettingsFactory
{
    /**
     * @return \Generator<ReindexSettings>
     */
    public function generateSettings(IndexCollection $collection, ReindexConfig $config): \Generator
    {
        $sortByName = Criteria::create();
        $sortByName->orderBy(['name' => 'desc']);

        $collection = $collection->matching($sortByName);

        $maxDate = $this->initMaxDate($config);
        $lastTargetIndex = null;
        $targetCollection = IndexCollection::createEmpty();

        foreach ($collection as $indexName => $index) {
            $dateStr = \preg_replace("/{$config->prefix}/", '', $indexName);
            $targetIndex = \sprintf('%s%s', $config->prefix, \mb_substr($dateStr, 0, -3));
            $next = null !== $lastTargetIndex && $lastTargetIndex !== $targetIndex;

            if (true === $next) {
                yield ReindexSettings::create($targetCollection, $lastTargetIndex);
                $targetCollection = IndexCollection::createEmpty();
            }

            $indexDate = $this->createIndexDate($config, $dateStr);

            if ($indexDate->lessThanOrEqualTo($maxDate)) {
                $targetCollection->set($indexName, $index);
                $lastTargetIndex = $targetIndex;
            }
        }

        if ($targetCollection->count()) {
            yield ReindexSettings::create($targetCollection, $lastTargetIndex);
        }
    }

    private function initMaxDate(ReindexConfig $config): Carbon
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

    private function createIndexDate(ReindexConfig $config, string $dateStr): Carbon
    {
        if ($config->from->equals(PartitionType::DAILY())) {
            $indexDate = Carbon::createFromFormat('Y-m-d', $dateStr);
        } elseif ($config->from->equals(PartitionType::MONTHLY())) {
            $indexDate = Carbon::createFromFormat('Y-m', $dateStr);
        }

        return $indexDate;
    }
}
