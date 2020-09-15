<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Input\Exception;

use Throwable;

/**
 * Class InvalidPartitionTypeException.
 */
final class InvalidPartitionTypeException extends \InvalidArgumentException
{
    public function __construct(string $value, string $argument, Throwable $previous = null)
    {
        $message = \sprintf('"%s" is not a valid partition type for "%s"', $value, $argument);
        parent::__construct($message, 0, $previous);
    }
}
