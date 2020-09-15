<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\Exception\MissingClientException;
use Basster\Reindexr\ElasticSearch\Exception\MissingConfigException;
use Basster\Reindexr\ElasticSearch\IndexCollection;
use Basster\Reindexr\ElasticSearch\ReindexSettingsFactory;
use Basster\Reindexr\Input\ReindexConfig;
use Elastica\Client;

/**
 * Class AbstractIndicesHandler.
 */
abstract class AbstractIndicesHandler implements IndicesHandler
{
    private ?Client $client = null;
    private ?IndicesHandler $nextHandler = null;
    private ?ReindexConfig $reindexConfig = null;
    private ReindexSettingsFactory $settingsFactory;

    public function __construct(ReindexSettingsFactory $settingsFactory)
    {
        $this->settingsFactory = $settingsFactory;
    }

    public function setConfig(ReindexConfig $reindexConfig): void
    {
        $this->reindexConfig = $reindexConfig;
    }

    public function setNext(IndicesHandler $next): IndicesHandler
    {
        $this->nextHandler = $next;

        return $next;
    }

    public function handle(IndexCollection $indices): ?IndexCollection
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($indices);
        }

        return null;
    }

    public function getClient(): Client
    {
        if (!$this->client) {
            throw new MissingClientException();
        }

        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getConfig(): ReindexConfig
    {
        if (!$this->reindexConfig) {
            throw new MissingConfigException();
        }

        return $this->reindexConfig;
    }

    protected function getReindexSettings(IndexCollection $indices): \Generator
    {
        return $this->settingsFactory->generateSettings($indices, $this->getConfig());
    }
}
