<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Basster\Reindexr\ElasticSearch\Exception\NoIndicesFoundException;
use Basster\Reindexr\ElasticSearch\Exception\UnequalAliasesException;
use Basster\Reindexr\ElasticSearch\Exception\UnequalMappingsException;
use Basster\Reindexr\ElasticSearch\Exception\UnequalSettingsException;
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

    private function arrayEquals(array $a, array $b): bool
    {
        return 0 === \strcmp(
            \json_encode($a, JSON_THROW_ON_ERROR),
            \json_encode($b, JSON_THROW_ON_ERROR)
        );
    }
}
