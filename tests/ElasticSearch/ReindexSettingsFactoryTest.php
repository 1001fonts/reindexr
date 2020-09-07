<?php
declare(strict_types=1);

namespace Basster\Reindexr\Tests\ElasticSearch;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Basster\Reindexr\ElasticSearch\ReindexSettingsFactory;
use Basster\Reindexr\Input\ReindexConfig;
use Basster\Reindexr\PartitionType;
use Carbon\Carbon;
use Elastica\Client;
use Elastica\Index;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Basster\Reindexr\ElasticSearch\ReindexSettingsFactory
 */
final class ReindexSettingsFactoryTest extends TestCase
{
    private $client;
    private array $indexNames = [
        'foobar_2020-09-06',
        'foobar_2020-08-21',
        'foobar_2020-08-20',
        'foobar_2020-08-19',
        'foobar_2020-08-18',
        'foobar_2020-07-12',
        'foobar_2020-07-11',
        'foobar_2019-07-10',
        'foobar_2019-06-09',
        'foobar_2018-05-11',
        'foobar_2018-05-10',
        'foobar_2019-06',
        'foobar_2019-05',
        'foobar_2018-06',
        'foobar_2018-05',
        'foobar_2018-04',
        'foobar_2018-04',
        'foobar_2017',
        'foobar_2016',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        Carbon::setTestNow(Carbon::createFromDate(2020, 9, 7));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    /**
     * @test
     */
    public function createReindexSettingsForDailyToMonthly(): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::DAILY(), PartitionType::MONTHLY(), false);
        $collection = $this->createIndexCollection();

        $factory = new ReindexSettingsFactory();
        $settings = $factory->generateSettings($collection, $config)->current();

        $this->assert2018DailyIndices($settings);
    }

    /**
     * @test
     */
    public function createReindexSettingsFor2018(): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::DAILY(), PartitionType::MONTHLY(), false);
        $collection = $this->createIndexCollection([
            'foobar_2020-08-21',
            'foobar_2020-08-20',
            'foobar_2020-08-19',
            'foobar_2020-08-18',
        ]);

        $factory = new ReindexSettingsFactory();
        $settings = $factory->generateSettings($collection, $config)->current();

        $this->assert2018DailyIndices($settings);
    }

    /**
     * @test
     */
    public function createReindexSettingsForDailyToMonthlyIncludeCurrent(): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::DAILY(), PartitionType::MONTHLY(), true);
        $collection = $this->createIndexCollection();

        $factory = new ReindexSettingsFactory();

        $settingsGenerator = $factory->generateSettings($collection, $config);

        $currentMonthSettings = $settingsGenerator->current();
        self::assertCount(1, $currentMonthSettings->sourceIndices);
        self::assertSame('foobar_2020-09', $currentMonthSettings->toIndex);
        self::assertSame(['foobar_2020-09-06'], $currentMonthSettings->sourceIndices->getKeys());

        $settingsGenerator->next();

        $settings2018 = $settingsGenerator->current();

        $this->assert2018DailyIndices($settings2018);
    }

    private function createIndexCollection(array $indexNames = []): IndexCollection
    {
        $indexNames = $indexNames ?: $this->indexNames;
        $collection = IndexCollection::createEmpty();
        foreach ($indexNames as $name) {
            $collection->set($name, new Index($this->client, $name));
        }

        return $collection;
    }

    /**
     * @param $settings
     */
    private function assert2018DailyIndices($settings): void
    {
        self::assertCount(4, $settings->sourceIndices);
        self::assertSame('foobar_2020-08', $settings->toIndex);
        self::assertSame(
            [
                'foobar_2020-08-21',
                'foobar_2020-08-20',
                'foobar_2020-08-19',
                'foobar_2020-08-18',
            ],
            $settings->sourceIndices->getKeys()
        );
    }
}
