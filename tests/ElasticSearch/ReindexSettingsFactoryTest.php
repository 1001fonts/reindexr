<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\ElasticSearch;

use Carbon\Carbon;
use Elastica\Client;
use Elastica\Index;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettings;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettingsFactory;
use Maxfonts\Reindexr\Input\ReindexConfig;
use Maxfonts\Reindexr\PartitionType;
use PHPUnit\Framework\TestCase;

/**
 * @internal
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
        'foobar_2018-03',
        'foobar_2017',
        'foobar_2016',
    ];
    private ReindexSettingsFactory $settingsFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        $this->settingsFactory = new ReindexSettingsFactory();
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

        $settings = $this->settingsFactory->generateSettings($collection, $config)->current();

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

        $settings = $this->settingsFactory->generateSettings($collection, $config)->current();

        $this->assert2018DailyIndices($settings);
    }

    /**
     * @test
     */
    public function createReindexSettingsForDailyToMonthlyIncludeCurrent(): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::DAILY(), PartitionType::MONTHLY(), true);
        $collection = $this->createIndexCollection();

        $settingsGenerator = $this->settingsFactory->generateSettings($collection, $config);

        $currentMonthSettings = $settingsGenerator->current();
        self::assertCount(1, $currentMonthSettings->sourceIndices);
        self::assertSame('foobar_2020-09', $currentMonthSettings->toIndex);
        self::assertSame(['foobar_2020-09-06'], $currentMonthSettings->sourceIndices->getKeys());

        $settingsGenerator->next();

        $settings2018 = $settingsGenerator->current();

        $this->assert2018DailyIndices($settings2018);
    }

    /**
     * @test
     */
    public function createReindexSettingsFor(): void
    {
        $config = ReindexConfig::create('1kf_activity_dev_2020-02', PartitionType::DAILY(), PartitionType::MONTHLY(), true);
        $indexNames = [
            '1kf_activity_dev_2020-02-27',
            '1kf_activity_dev_2020-02-25',
            '1kf_activity_dev_2020-02-17',
            '1kf_activity_dev_2020-02-11',
            '1kf_activity_dev_2020-02-04',
            '1kf_activity_dev_2020-02-03',
        ];
        $collection = $this->createIndexCollection($indexNames);

        $settingsGenerator = $this->settingsFactory->generateSettings($collection, $config);

        $currentMonthSettings = $settingsGenerator->current();
        self::assertSame('1kf_activity_dev_2020-02', $currentMonthSettings->toIndex);
        self::assertSame($indexNames, $currentMonthSettings->sourceIndices->getKeys());
    }

    /**
     * @test
     */
    public function createReindexSettingsForMonthlyToYearly(): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::MONTHLY(), PartitionType::YEARLY(), true);
        $collection = $this->createIndexCollection();

        $generator = $this->settingsFactory->generateSettings($collection, $config);
        /** @var ReindexSettings $settings */
        $settings = $generator->current();

        self::assertCount(2, $settings->sourceIndices);
        self::assertSame('foobar_2019', $settings->toIndex);

        $generator->next();

        /** @var ReindexSettings $settings */
        $settings = $generator->current();

        self::assertCount(4, $settings->sourceIndices);
        self::assertSame('foobar_2018', $settings->toIndex);
    }

    /**
     * @test
     */
    public function createReindexSettingsForMonthlyToYearlyWithOnlyOneIndexPerTarget(): void
    {
        $config = ReindexConfig::create('1kf_download_prod', PartitionType::MONTHLY(), PartitionType::YEARLY(), true);
        $indices = [
            '1kf_download_prod_2016-03',
            '1kf_download_prod_2020-07',
        ];

        $collection = $this->createIndexCollection($indices);

        $generator = $this->settingsFactory->generateSettings($collection, $config);
        /** @var ReindexSettings $settings */
        $settings = $generator->current();

        self::assertCount(1, $settings->sourceIndices);
        self::assertSame('1kf_download_prod_2020', $settings->toIndex);

        $generator->next();

        /** @var ReindexSettings $settings */
        $settings = $generator->current();

        self::assertCount(1, $settings->sourceIndices);
        self::assertSame('1kf_download_prod_2016', $settings->toIndex);
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
