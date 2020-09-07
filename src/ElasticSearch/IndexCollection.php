<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Basster\Reindexr\ElasticSearch\Exception\NoIndicesFoundException;
use Basster\Reindexr\ElasticSearch\Exception\UnequalAliasesException;
use Basster\Reindexr\ElasticSearch\Exception\UnequalMappingsException;
use Basster\Reindexr\ElasticSearch\Exception\UnequalSettingsException;
use Basster\Reindexr\Input\ReindexConfig;
use Basster\Reindexr\PartitionType;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Doctrine\Common\Collections\ArrayCollection;
use Elastica\Client;
use Elastica\Index;
use Elastica\Response;

/**
 * Class IndexCollection.
 */
final class IndexCollection extends ArrayCollection
{
    private function __construct(array $elements = [])
    {
        parent::__construct($elements);
    }

    public static function createFromResponse(Response $response, Client $client): self
    {
        $data = $response->getData();

        $collection = new self();

        foreach (\array_keys($data['metadata']['indices']) as $name) {
            $collection->set($name, new Index($client, $name));
        }

        return $collection;
    }

    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @throws UnequalMappingsException
     * @throws \JsonException
     */
    public function getMapping(): array
    {
        $mapping = $this->first()->getMapping();
        /** @var Index $index */
        foreach ($this as $index) {
            if (!$this->arrayEquals($mapping, $index->getMapping())) {
                throw new UnequalMappingsException($index->getName());
            }
        }

        return $mapping;
    }

    public function getSettings(): array
    {
        $settings = IndexSettings::fromElasticaSettings($this->first()->getSettings());

        /** @var Index $index */
        foreach ($this as $index) {
            if (!$settings->equals(IndexSettings::fromElasticaSettings($index->getSettings()))) {
                throw new UnequalSettingsException($index->getName());
            }
        }

        return $settings->asArray();
    }

    public function getAliases(): array
    {
        $aliases = $this->first()->getAliases();
        /** @var Index $index */
        foreach ($this as $index) {
            if (!$this->arrayEquals($aliases, $index->getAliases())) {
                throw new UnequalAliasesException($index->getName());
            }
        }

        return \array_combine($aliases, \array_fill(0, \count($aliases), new \stdClass()));
    }

    public function first(): Index
    {
        if (!$this->count()) {
            throw new NoIndicesFoundException();
        }

        return parent::first();
    }

    public function filterByConfig(ReindexConfig $config): self
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

        $filter = static function (Index $index, string $indexName) use ($config, $maxDate) {
            $dateStr = \preg_replace("/{$config->prefix}/", '', $indexName);

            try {
                if ($config->from->equals(PartitionType::DAILY())) {
                    $indexDate = Carbon::createFromFormat('Y-m-d', $dateStr);
                } elseif ($config->from->equals(PartitionType::MONTHLY())) {
                    $indexDate = Carbon::createFromFormat('Y-m', $dateStr);
                }
                if ($indexDate->lessThanOrEqualTo($maxDate)) {
                    return $index;
                }
            } catch (InvalidFormatException $ex) {
                return null;
            }

            return null;
        };

        return new self(\array_filter($this->toArray(), $filter, ARRAY_FILTER_USE_BOTH));
    }

    private function arrayEquals(array $a, array $b): bool
    {
        return 0 === \strcmp(
            \json_encode($a, JSON_THROW_ON_ERROR),
            \json_encode($b, JSON_THROW_ON_ERROR)
        );
    }
}
