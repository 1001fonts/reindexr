<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Basster\Reindexr\ElasticSearch\ReindexSettings;
use Elastica\Index;

/**
 * Class CloseIndicesHandler.
 */
final class CloseIndicesHandler extends AbstractIndicesHandler
{
    public function handle(IndexCollection $indices): ?IndexCollection
    {
        /** @var ReindexSettings $setting */
        foreach ($this->getReindexSettings($indices) as $setting) {
            /** @var Index $index */
            foreach ($setting->sourceIndices as $index) {
                $index->close();
            }
        }

        return parent::handle($indices);
    }
}
