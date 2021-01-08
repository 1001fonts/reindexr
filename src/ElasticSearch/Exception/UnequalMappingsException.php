<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Exception;

/**
 * Class UnequalMappingsException.
 */
final class UnequalMappingsException extends UnequalMetadataException
{
    protected function getMetadataType(): string
    {
        return 'mappings';
    }
}
