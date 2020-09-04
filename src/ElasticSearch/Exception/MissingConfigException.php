<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Exception;

use Throwable;

/**
 * Class MissingConfigException.
 */
final class MissingConfigException extends \RuntimeException
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct('Cannot find the Reindex Config!', $code, $previous);
    }
}
