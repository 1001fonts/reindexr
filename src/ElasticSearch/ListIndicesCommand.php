<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;
use Elastica\Client;

/**
 * Class ListIndicesCommand
 * @package Basster\Reindexr\ElasticSearch
 */
final class ListIndicesCommand implements ElasticsearchCommand
{
    public function run(Client $client)
    {
        $metadataResponse = $client->request('_cluster/state/metadata/1kf_*');
        $indices = IndexCollection::createFromResponse($metadataResponse, $client);
    }
}
