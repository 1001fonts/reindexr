<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Handler;

use Elastica\Index;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettings;
use Maxfonts\Reindexr\Event\IndexClosedEvent;

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
