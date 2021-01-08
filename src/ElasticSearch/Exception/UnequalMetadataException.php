<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Exception;

use Throwable;

/**
 * Class UnequalMetadataException.
 */
abstract class UnequalMetadataException extends \RuntimeException
{
    public array $targetMetadata = [];
    public array $conflictingMetadata = [];

    private function __construct(string $index, Throwable $previous = null)
    {
        parent::__construct("Unequal {$this->getMetadataType()} found in index {$index}. These indices cannot be merged.", 0, $previous);
    }

    public static function createWithContext(
        string $index,
        array $targetMetadata,
        array $conflictingMetadata,
        Throwable $previous = null
    ): self {
        $exception = new static($index, $previous);
        $exception->targetMetadata = $targetMetadata;
        $exception->conflictingMetadata = $conflictingMetadata;

        return $exception;
    }

    abstract protected function getMetadataType(): string;
}
