<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

/**
 * Class ReindexSettings.
 */
final class ReindexSettings
{
    public IndexCollection $sourceIndices;
    public string $toIndex;

    /**
     * ReindexSettings constructor.
     */
    private function __construct(IndexCollection $sourceIndices, string $toIndex)
    {
        $this->sourceIndices = $sourceIndices;
        $this->toIndex = $toIndex;
    }

    public static function create(IndexCollection $sourceIndices, string $toIndex): self
    {
        return new self($sourceIndices, $toIndex);
    }
}
