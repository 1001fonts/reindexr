<?php
declare(strict_types=1);

namespace Basster\Reindexr\Tests\ElasticSearch;

use Basster\Reindexr\ElasticSearch\Exception\NoIndicesFoundException;
use Basster\Reindexr\ElasticSearch\IndexCollection;
use Basster\Reindexr\Input\ReindexConfig;
use Basster\Reindexr\PartitionType;
use Carbon\Carbon;
use Elastica\Client;
use Elastica\Index;
use Elastica\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexCollectionTest.
 *
 * @covers \Basster\Reindexr\ElasticSearch\IndexCollection
 *
 * @internal
 */
final class IndexCollectionTest extends TestCase
{
    private $client;
    private array $indexNames = [
        'foobar_2020-09-07',
        'foobar_2020-08-21',
        'foobar_2020-08-20',
        'foobar_2020-08-19',
        'foobar_2020-08-18',
        'foobar_2020-07-12',
        'foobar_2020-07-11',
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
    public function firstThrowsNoIndicesFoundExceptionWhenEmpty(): void
    {
        $this->expectException(NoIndicesFoundException::class);

        $response = new Response(['metadata' => ['indices' => []]]);
        $collection = IndexCollection::createFromResponse($response, $this->createMock(Client::class));
        $collection->first();
    }

    /**
     * @test
     * @dataProvider provideIncludeCurrent
     */
    public function filterIndicesGetAllDailyIndicesWithoutActualMonth(bool $includeCurrent): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::DAILY(), PartitionType::MONTHLY(), $includeCurrent);
        $collection = $this->createIndexCollection();

        $collection = $collection->filterByConfig($config);

        $expectedCount = $includeCurrent ? \count($this->indexNames) : \count($this->indexNames) - 1;

        self::assertCount($expectedCount, $collection);
        self::assertEquals($includeCurrent, $collection->containsKey('foobar_2020-09-07'));
    }

    public function provideIncludeCurrent(): iterable
    {
        yield "don't include current" => [false];
        yield 'include current' => [true];
    }

    /**
     * @test
     * @dataProvider provideIncludeCurrent
     */
    public function filterIndicesGetAllMonthlyIndicesWithoutActualYear(bool $includeCurrent): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::MONTHLY(), PartitionType::YEARLY(), $includeCurrent);

        $collection = $this->createIndexCollection([
            'foobar_2020-01',
            'foobar_2020-02',
            'foobar_2019-12',
            'foobar_2019-11',
            'foobar_2019-10',
        ]);

        $collection = $collection->filterByConfig($config);

        $expectedCount = $includeCurrent ? 5 : 3;

        self::assertCount($expectedCount, $collection);
        self::assertSame($includeCurrent, $collection->containsKey('foobar_2020-01'));
        self::assertSame($includeCurrent, $collection->containsKey('foobar_2020-02'));
    }

    /**
     * @test
     * @dataProvider provideIndexNamesForGetDailyIndicesLeaveOut
     */
    public function filterIndicesGetDailyIndicesLeaveOutMonthly(string $filteredIndexName, array $indexNames): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::DAILY(), PartitionType::MONTHLY(), false);

        $collection = $this->createIndexCollection($indexNames);

        $collection = $collection->filterByConfig($config);

        self::assertCount(2, $collection);
        self::assertFalse($collection->containsKey($filteredIndexName));
    }

    public function provideIndexNamesForGetDailyIndicesLeaveOut(): iterable
    {
        $indexNames = [
            'foobar_2020-07-12',
            'foobar_2020-07-11',
            'foobar_2020-08',
            'foobar_2020',
        ];

        yield 'leave out monthly' => ['foobar_2020-08', $indexNames];
        yield 'leave out yearly' => ['foobar_2020', $indexNames];
    }

    /**
     * @test
     * @dataProvider provideIndexNamesForGetMonthlyIndicesLeaveOut
     */
    public function filterIndicesGetMonthlyIndicesLeaveOutDaily(string $filteredIndex, array $indexNames): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::MONTHLY(), PartitionType::YEARLY(), false);

        $collection = $this->createIndexCollection($indexNames);

        $collection = $collection->filterByConfig($config);

        self::assertCount(1, $collection);
        self::assertFalse($collection->containsKey($filteredIndex));
    }

    public function provideIndexNamesForGetMonthlyIndicesLeaveOut(): iterable
    {
        $indexNames = [
            'foobar_2020-08-12',
            'foobar_2019-07',
            'foobar_2018',
        ];

        yield 'leave out daily' => ['foobar_2020-08-12', $indexNames];
        yield 'leave out yearly' => ['foobar_2018', $indexNames];
    }

    public function leaveOutIndicesWithoutCorrectPrefix(): void
    {
        $config = ReindexConfig::create('foobar_', PartitionType::DAILY(), PartitionType::MONTHLY(), false);

        $indexNames = [
            'foobar_2020-08-12',
            'foobar_2020-08-11',
            'barbaz_2020-08-11',
        ];

        $collection = $this->createIndexCollection($indexNames);

        $collection = $collection->filterByConfig($config);

        self::assertCount(2, $collection);
        self::assertFalse($collection->containsKey('barbaz_2020-08-11'));
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
}
