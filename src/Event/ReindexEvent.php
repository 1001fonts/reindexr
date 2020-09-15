<?php
declare(strict_types=1);

namespace Basster\Reindexr\Event;

use Basster\Reindexr\ElasticSearch\ReindexSettings;

/**
 * Class ReindexEvent.
 */
final class ReindexEvent
{
    public ReindexSettings $settings;

    /**
     * ReindexEvent constructor.
     */
    private function __construct(ReindexSettings $settings)
    {
        $this->settings = $settings;
    }

    public static function create(ReindexSettings $settings): self
    {
        return new self($settings);
    }
}
