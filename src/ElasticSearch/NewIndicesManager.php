<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch;

use Elastica\Index;
use Maxfonts\Reindexr\Event\TargetIndexCreatedEvent;
use Maxfonts\Reindexr\Event\TargetIndexRollbackEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class NewIndicesManager.
 */
final class NewIndicesManager
{
    private IndexCollection $indices;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * NewIndicesManager constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->indices = IndexCollection::createEmpty();
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addIndex(Index $index): void
    {
        $this->indices->add($index);
        $this->eventDispatcher->dispatch(TargetIndexCreatedEvent::create($index));
    }

    public function rollback(): void
    {
        $this->eventDispatcher->dispatch(TargetIndexRollbackEvent::create(...$this->indices));
        /** @var Index $index */
        foreach ($this->indices as $index) {
            $index->delete();
        }
    }
}
