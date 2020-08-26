<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;
use Elastica\Client;

/**
 * Class ElasticsearchCommand
 * @package Basster\Reindexr\ElasticSearch
 */
interface ElasticsearchCommand
{
    public function run(Client $client);
}
