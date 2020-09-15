<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Handler;

use Elastica\Client;
use Maxfonts\Reindexr\ElasticSearch\Exception\MissingClientException;
use Maxfonts\Reindexr\ElasticSearch\Exception\MissingConfigException;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettingsFactoryInterface;
use Maxfonts\Reindexr\Input\ReindexConfig;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractIndicesHandler.
 */
abstract class AbstractIndicesHandler implements IndicesHandler
{
    private ?Client $client = null;
    private ?IndicesHandler $nextHandler = null;
    private ?ReindexConfig $reindexConfig = null;
    private ReindexSettingsFactoryInterface $settingsFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(ReindexSettingsFactoryInterface $settingsFactory, EventDispatcherInterface $eventDispatcher)
    {
        $this->settingsFactory = $settingsFactory;
        $this->eventDispatcher = $eventDispatcher;
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

    protected function dispatchEvent(object $event): object
    {
        return $this->eventDispatcher->dispatch($event);
    }

    protected function getReindexSettings(IndexCollection $indices): \Generator
    {
        return $this->settingsFactory->generateSettings($indices, $this->getConfig());
    }
}
