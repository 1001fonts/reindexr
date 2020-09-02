<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Index;

/**
 * Class CloseIndicesHandler.
 */
final class CloseIndicesHandler extends AbstractIndicesHandler
{
    public function handle(IndexCollection $indices): ?IndexCollection
    {
        /** @var Index $index */
        foreach ($indices as $index) {
            $index->close();
        }

        return parent::handle($indices);
    }
}
