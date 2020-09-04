<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Exception;

use Throwable;

/**
 * Class MissingClientException.
 */
final class MissingClientException extends ElasticsearchException
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct('Cannot find the Elastica Client!', $code, $previous);
    }
}
