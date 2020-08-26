<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Exception;

use Throwable;

/**
 * Class UnequalMappingsException.
 */
final class UnequalMappingsException extends UnequalMetadataException
{
    public function __construct(string $index, Throwable $previous = null)
    {
        parent::__construct($index, 'mappings', $previous);
    }
}
