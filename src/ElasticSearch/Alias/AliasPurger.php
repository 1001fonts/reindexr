<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Alias;

use Elastica\Index;

/**
 * Class AliasPurger.
 */
final class AliasPurger implements IndexAwarePurger
{
    public function purge(Index $index): void
    {
        $index->removeAlias('*');
    }
}
