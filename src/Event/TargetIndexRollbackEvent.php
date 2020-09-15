<?php
declare(strict_types=1);

namespace Basster\Reindexr\Event;

use Elastica\Index;

/**
 * Class TargetIndexRollbackEvent.
 */
final class TargetIndexRollbackEvent
{
    private array $indices;

    private function __construct(Index ...$indices)
    {
        $this->indices = $indices;
    }

    public static function create(Index ...$indices): self
    {
        return new self(...$indices);
    }

    public function getNames(): array
    {
        return \array_map(fn (Index $index) => $index->getName(), $this->indices);
    }
}
