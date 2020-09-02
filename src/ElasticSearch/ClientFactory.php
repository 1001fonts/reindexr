<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Elastica\Client;

/**
 * Class ClientFactory.
 */
final class ClientFactory
{
    public function create(string $host, int $port): Client
    {
        return new Client(['host' => $host, 'port' => $port]);
    }
}
