<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Request;

/**
 * Class ReindexHandler.
 */
final class ReindexHandler extends AbstractIndicesHandler
{
    public function handle(IndexCollection $indices): ?IndexCollection
    {
        $mergeIndexName = '1kf_download_dev_2020-08';

        $this->getClient()->request('_reindex?wait_for_completion=true', Request::POST, [
            'source' => [
                'index' => $indices->getKeys(),
            ],
            'dest' => [
                'index' => $mergeIndexName,
            ],
        ]);

        return parent::handle($indices);
    }
}
