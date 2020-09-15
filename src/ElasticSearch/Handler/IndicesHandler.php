<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Handler;

use Elastica\Client;
use Maxfonts\Reindexr\ElasticSearch\Exception\MissingClientException;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\Input\ReindexConfig;

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

    public function getConfig(): ReindexConfig;
}
