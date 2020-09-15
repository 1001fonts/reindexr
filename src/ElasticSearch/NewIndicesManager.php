<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Elastica\Index;

/**
 * Class NewIndicesManager.
 */
final class NewIndicesManager
{
    private IndexCollection $indices;

    /**
     * NewIndicesManager constructor.
     */
    public function __construct()
    {
        $this->indices = IndexCollection::createEmpty();
    }

    public function addIndex(Index $index): void
    {
        $this->indices->add($index);
    }

    public function rollback(): void
    {
        /** @var Index $index */
        foreach ($this->indices as $index) {
            $index->delete();
        }
    }
}
