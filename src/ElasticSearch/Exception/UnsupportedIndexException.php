<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Exception;

use Throwable;

/**
 * Class UnsupportedIndexException.
 */
final class UnsupportedIndexException extends \LogicException
{
    public function __construct(string $index, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("{$index} is not supported here.", $code, $previous);
    }
}
