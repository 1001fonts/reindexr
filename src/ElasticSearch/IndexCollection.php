<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;
use Elastica\Client;
use Elastica\Index;
use Elastica\Response;

/**
 * Class IndexCollection
 * @package Basster\Reindexr\ElasticSearch
 */
final class IndexCollection
{
    private array $list = [];

    public static function createFromResponse(Response $response, Client $client):self
    {
        $data = $response->getData();

        $collection = new self;

        foreach ($data['metadata']['indices'] as $name => $index) {
            $collection->list[] = new Index($client, $name);
        }

        return $collection;
    }
}
