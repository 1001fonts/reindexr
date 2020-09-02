<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Index;

/**
 * Class CreateTargetIndexHandler.
 */
final class CreateTargetIndexHandler extends AbstractIndicesHandler
{
    public function handle(IndexCollection $indices): ?IndexCollection
    {
        $mergeIndexName = '1kf_download_dev_2020-08';

        $index = new Index($this->getClient(), $mergeIndexName);
        $index->create([
            'settings' => $indices->getSettings(),
            'mappings' => $indices->getMapping(),
            'aliases' => $indices->getAliases(),
        ]);

        while (!$index->exists()) {
            \sleep(1);
        }

        return parent::handle($indices);
    }
}
