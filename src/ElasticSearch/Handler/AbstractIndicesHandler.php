<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\Exception\MissingClientException;
use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Client;

/**
 * Class AbstractIndicesHandler.
 */
abstract class AbstractIndicesHandler implements IndicesHandler
{
    private ?Client $client = null;
    private ?IndicesHandler $nextHandler = null;

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
}
