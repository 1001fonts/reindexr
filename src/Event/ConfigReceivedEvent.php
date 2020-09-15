<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Event;

use Maxfonts\Reindexr\Input\ReindexConfig;

/**
 * Class ConfigReceivedEvent.
 */
final class ConfigReceivedEvent
{
    public ReindexConfig $config;

    /**
     * ConfigReceivedEvent constructor.
     */
    private function __construct(ReindexConfig $config)
    {
        $this->config = $config;
    }

    public static function create(ReindexConfig $config): self
    {
        return new self($config);
    }
}
