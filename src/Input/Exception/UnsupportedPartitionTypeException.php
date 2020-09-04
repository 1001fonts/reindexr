<?php
declare(strict_types=1);

namespace Basster\Reindexr\Input\Exception;

use Throwable;

/**
 * Class UnsupportedPartitionTypeException.
 */
final class UnsupportedPartitionTypeException extends \LogicException
{
    public function __construct(string $value, string $argument, Throwable $previous = null)
    {
        $message = \sprintf('"%s" is not a supported partition type for "%s"', $value, $argument);
        parent::__construct($message, 0, $previous);
    }
}
