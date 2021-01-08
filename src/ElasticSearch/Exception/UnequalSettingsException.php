<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Exception;

/**
 * Class UnequalSettingsException.
 */
final class UnequalSettingsException extends UnequalMetadataException
{
    protected function getMetadataType(): string
    {
        return 'settings';
    }
}
