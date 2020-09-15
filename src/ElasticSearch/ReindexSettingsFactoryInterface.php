<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Basster\Reindexr\Input\ReindexConfig;

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
