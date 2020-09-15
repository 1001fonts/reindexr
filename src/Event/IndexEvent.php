<?php
declare(strict_types=1);

namespace Basster\Reindexr\Event;

use Elastica\Index;

/**
 * Class IndexEvent.
 */
abstract class IndexEvent
{
    public Index $index;

    /**
     * TargetIndexCreatedEvent constructor.
     */
    private function __construct(Index $index)
    {
        $this->index = $index;
    }

    public static function create(Index $index): self
    {
        return new static($index);
    }
}
