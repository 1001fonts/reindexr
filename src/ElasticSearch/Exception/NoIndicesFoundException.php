<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Exception;

use Throwable;

/**
 * Class NoIndicesFoundException.
 */
final class NoIndicesFoundException extends ElasticsearchException
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct('No Indices found for the given pattern!', $code, $previous);
    }
}
