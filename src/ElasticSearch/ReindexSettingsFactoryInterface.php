<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch;

use Maxfonts\Reindexr\Input\ReindexConfig;

/**
 * Class ReindexSettingsFactory.
 */
interface ReindexSettingsFactoryInterface
{
    /**
     * @return \Generator<ReindexSettings>
     */
    public function generateSettings(IndexCollection $collection, ReindexConfig $config): \Generator;
}
