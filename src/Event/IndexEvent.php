<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Event;

use Elastica\Index;

/**
 * Class IndexEvent.
 *
 * @psalm-consistent-constructor
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
