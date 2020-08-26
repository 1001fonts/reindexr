<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Elastica\Client;

/**
 * Class ElasticsearchCommand.
 */
interface ElasticsearchCommand
{
    public function run(Client $client);
}
