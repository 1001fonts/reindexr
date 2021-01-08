<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Elastica\Client;
use Elastica\Index;
use Elastica\Response;
use Maxfonts\Reindexr\ElasticSearch\Exception\NoIndicesFoundException;
use Maxfonts\Reindexr\ElasticSearch\Exception\UnequalAliasesException;
use Maxfonts\Reindexr\ElasticSearch\Exception\UnequalMappingsException;
use Maxfonts\Reindexr\ElasticSearch\Exception\UnequalSettingsException;

/**
 * Class IndexCollection.
 *
 * @extends ArrayCollection<string, Index>
 *
 * @method self matching(Criteria $criteria)
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
     * @throws NoIndicesFoundException
     * @throws \JsonException
     */
    public function getMapping(): array
    {
        $mapping = $this->first()->getMapping();
        /** @var Index $index */
        foreach ($this as $index) {
            $indexMapping = $index->getMapping();
            if (!$this->arrayEquals($mapping, $indexMapping)) {
                throw UnequalMappingsException::createWithContext($index->getName(), $mapping, $indexMapping);
            }
        }

        return $mapping;
    }

    /**
     * @throws NoIndicesFoundException
     */
    public function getSettings(): array
    {
        $settings = IndexSettings::fromElasticaSettings($this->first()->getSettings());

        /** @var Index $index */
        foreach ($this as $index) {
            $indexSettings = IndexSettings::fromElasticaSettings($index->getSettings());
            if (!$settings->equals($indexSettings)) {
                throw UnequalSettingsException::createWithContext($index->getName(), $settings->asArray(), $indexSettings->asArray());
            }
        }

        return $settings->asArray();
    }

    public function getAliases(): array
    {
        $aliases = $this->first()->getAliases();
        /** @var Index $index */
        foreach ($this as $index) {
            $indexAliases = $index->getAliases();
            if (!$this->arrayEquals($aliases, $indexAliases)) {
                throw UnequalAliasesException::createWithContext($index->getName(), $aliases, $indexAliases);
            }
        }

        return \array_combine($aliases, \array_fill(0, \count($aliases), new \stdClass()));
    }

    /**
     * @psalm-return Index
     *
     * @throws NoIndicesFoundException
     */
    public function first(): Index
    {
        $first = parent::first();

        if (false === $first) {
            throw new NoIndicesFoundException();
        }

        return $first;
    }

    /**
     * @return static
     * @psalm-return IndexCollection
     * @psalm-suppress LessSpecificImplementedReturnType
     */
    protected function createFrom(array $elements): self
    {
        return new static($elements);
    }

    private function arrayEquals(array $a, array $b): bool
    {
        return 0 === \strcmp(
            \json_encode($a, JSON_THROW_ON_ERROR),
            \json_encode($b, JSON_THROW_ON_ERROR)
        );
    }
}
