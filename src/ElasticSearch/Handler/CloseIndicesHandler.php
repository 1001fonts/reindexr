<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Basster\Reindexr\ElasticSearch\ReindexSettings;
use Basster\Reindexr\Event\IndexClosedEvent;
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
                $this->closeIndex($index);
            }
        }

        return parent::handle($indices);
    }

    private function closeIndex(Index $index): void
    {
        $index->close();
        $this->dispatchEvent(IndexClosedEvent::create($index));
    }
}
