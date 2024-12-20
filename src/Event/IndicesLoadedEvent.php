<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Event;

use Maxfonts\Reindexr\ElasticSearch\IndexCollection;

/**
 * Class IndicesLoadedEvent.
 */
final class IndicesLoadedEvent
{
    private IndexCollection $indices;

    /**
     * IndicesLoadedEvent constructor.
     */
    private function __construct(IndexCollection $indices)
    {
        $this->indices = $indices;
    }

    public static function create(IndexCollection $indices): self
    {
        return new self($indices);
    }

    public function getIndexNames(): array
    {
        return $this->indices->getKeys();
    }
}
