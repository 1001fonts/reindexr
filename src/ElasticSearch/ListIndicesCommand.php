<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Elastica\Client;
use Elastica\Index;
use Elastica\Request;

/**
 * Class ListIndicesCommand.
 */
final class ListIndicesCommand implements ElasticsearchCommand
{
    public function run(Client $client): void
    {
        $mergeIndexName = '1kf_download_dev_2020-08';
        $metadataResponse = $client->request('_cluster/state/metadata/1kf_download_dev_2020-08-*');
        $indices = IndexCollection::createFromResponse($metadataResponse, $client);

        $index = new Index($client, $mergeIndexName);
        $index->create([
            'settings' => $indices->getSettings(),
            'mappings' => $indices->getMapping(),
            'aliases' => $indices->getAliases(),
        ]);

        while (!$index->exists()) {
            \sleep(1);
        }

        $reindexResponse = $client->request('_reindex?wait_for_completion=true', Request::POST, [
            'source' => [
                'index' => $indices->getKeys(),
            ],
            'dest' => [
                'index' => $mergeIndexName,
            ],
        ]);

        /** @var Index $index */
        foreach ($indices as $index) {
            $index->close();
        }
    }
}
