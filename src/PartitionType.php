<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr;

use MyCLabs\Enum\Enum;

/**
 * Class PartitionType.
 *
 * @method static PartitionType DAILY()
 * @method static PartitionType MONTHLY()
 * @method static PartitionType YEARLY()
 * @psalm-immutable
 */
final class PartitionType extends Enum
{
    private const DAILY = 'daily';
    private const MONTHLY = 'monthly';
    private const YEARLY = 'yearly';
}
