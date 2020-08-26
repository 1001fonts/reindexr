<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Exception;

use Throwable;

/**
 * Class UnequalMetadataException.
 */
abstract class UnequalMetadataException extends \RuntimeException
{
    public function __construct(string $index, string $metadataType, Throwable $previous = null)
    {
        parent::__construct("Unequal {$metadataType} found in index {$index}. These indices cannot be merged.", 0, $previous);
    }
}
