<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Alias;

use Elastica\Index;

/**
 * Class IndexAwarePurger.
 */
interface IndexAwarePurger
{
    public function purge(Index $index): void;
}
