<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\Exception\MissingClientException;
use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Client;

/**
 * Class ElasticsearchCommand.
 */
interface IndicesHandler
{
    public function setNext(self $next): self;

    public function handle(IndexCollection $indices): ?IndexCollection;

    /**
     * @throws MissingClientException
     */
    public function getClient(): Client;
}
