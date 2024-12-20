<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\ElasticSearch;

use Maxfonts\Reindexr\ElasticSearch\IndexSettings;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexSettingsTest.
 *
 * @internal
 */
final class IndexSettingsTest extends TestCase
{
    /**
     * @test
     */
    public function equalsMustBeTrueWhenNumbersAreEqual(): void
    {
        $settingsA = IndexSettings::create(0, 1);
        $settingsB = IndexSettings::create(0, 1);

        self::assertTrue($settingsA->equals($settingsB));
    }
}
