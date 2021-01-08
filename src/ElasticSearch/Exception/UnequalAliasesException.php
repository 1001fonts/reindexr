<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Exception;

/**
 * Class UnequalSettingsException.
 */
final class UnequalAliasesException extends UnequalMetadataException
{
    protected function getMetadataType(): string
    {
        return 'aliases';
    }
}
