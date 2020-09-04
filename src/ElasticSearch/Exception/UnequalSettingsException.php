<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Exception;

/**
 * Class UnequalSettingsException.
 */
final class UnequalSettingsException extends UnequalMetadataException
{
    public function __construct(string $index, \Throwable $previous = null)
    {
        parent::__construct($index, 'settings', $previous);
    }
}
