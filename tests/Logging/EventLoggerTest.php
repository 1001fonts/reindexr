<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\Logging;

use Elastica\Index;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettings;
use Maxfonts\Reindexr\Event\ConfigReceivedEvent;
use Maxfonts\Reindexr\Event\IndexClosedEvent;
use Maxfonts\Reindexr\Event\IndicesLoadedEvent;
use Maxfonts\Reindexr\Event\ReindexEvent;
use Maxfonts\Reindexr\Event\TargetIndexCreatedEvent;
use Maxfonts\Reindexr\Event\TargetIndexRollbackEvent;
use Maxfonts\Reindexr\Input\ReindexConfig;
use Maxfonts\Reindexr\Logging\EventLogger;
use Maxfonts\Reindexr\PartitionType;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

/**
 * @internal
 */
final class EventLoggerTest extends TestCase
{
    use ProphecyTrait;

    public const INDEX_NAME = 'my-index';
    private LoggerInterface $logger;
    private EventLogger $eventLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new TestLogger();
        $this->eventLogger = new EventLogger($this->logger);
    }

    /**
     * @test
     * @dataProvider provideEventNames
     */
    public function subscribesEvent(string $eventName): void
    {
        $events = \iterator_to_array(EventLogger::getSubscribedEvents());
        $eventNames = \array_keys($events);

        self::assertContains($eventName, $eventNames);
    }

    public function provideEventNames(): iterable
    {
        yield ConfigReceivedEvent::class => [ConfigReceivedEvent::class];
        yield IndicesLoadedEvent::class => [IndicesLoadedEvent::class];
        yield TargetIndexCreatedEvent::class => [TargetIndexCreatedEvent::class];
        yield TargetIndexRollbackEvent::class => [TargetIndexRollbackEvent::class];
        yield ReindexEvent::class => [ReindexEvent::class];
        yield IndexClosedEvent::class => [IndexClosedEvent::class];
    }

    /**
     * @test
     */
    public function onIndexClosedWillLogTheIndexName(): void
    {
        $index = $this->createIndexMock();

        $this->eventLogger->onIndexClosed(IndexClosedEvent::create($index->reveal()));

        self::assertTrue($this->logger->hasInfoThatContains('Index "my-index" closed'));
    }

    /**
     * @test
     */
    public function onReindexEventWillLogIndexKeysAndTargetIndex(): void
    {
        $indices = $this->createIndexCollection();

        $settings = ReindexSettings::create($indices, 'to-index');

        $this->eventLogger->onReindexEvent(ReindexEvent::create($settings));

        self::assertTrue($this->logger->hasInfoThatContains('Reindex indices my-index into to-index'));
    }

    /**
     * @test
     */
    public function onConfigReceivedWillPutConfigIntoContext(): void
    {
        $config = ReindexConfig::create('foo', PartitionType::DAILY(), PartitionType::MONTHLY());
        $this->eventLogger->onConfigReceived(ConfigReceivedEvent::create($config));

        self::assertTrue($this->logger->hasInfoRecords());
        $infoRecord = $this->logger->records[0];
        self::assertSame($config->jsonSerialize(), $infoRecord['context']);
    }

    /**
     * @test
     */
    public function onIndicesLoadedWillHaveTheIndexNamesInContext(): void
    {
        $indices = $this->createIndexCollection();
        $this->eventLogger->onIndicesLoaded(IndicesLoadedEvent::create($indices));

        self::assertTrue($this->logger->hasInfoRecords());
        self::assertSame([self::INDEX_NAME], $this->logger->records[0]['context']);
    }

    /**
     * @test
     */
    public function onTargetIndexCreatedWillLogTheNameOfTheCreatedIndex(): void
    {
        $this->eventLogger->onTargetIndexCreated(TargetIndexCreatedEvent::create($this->createIndexMock()->reveal()));

        self::assertTrue($this->logger->hasInfoThatContains('Target index "my-index" created'));
    }

    /**
     * @test
     */
    public function onTargetIndexRollbackWillContainAllRollbackIndicesInContext(): void
    {
        $this->eventLogger->onTargetIndexRollback(TargetIndexRollbackEvent::create($this->createIndexMock()->reveal()));
        self::assertTrue($this->logger->hasInfoThatContains('Rollback target index creation'));
        self::assertSame(['indices' => [self::INDEX_NAME]], $this->logger->records[0]['context']);
    }

    /**
     * @return Index|\Prophecy\Prophecy\ObjectProphecy
     */
    private function createIndexMock()
    {
        $index = $this->prophesize(Index::class);
        $index->getName()->willReturn(self::INDEX_NAME);

        return $index;
    }

    private function createIndexCollection(): IndexCollection
    {
        $indices = IndexCollection::createEmpty();
        $indices->set(self::INDEX_NAME, $this->createIndexMock()->reveal());

        return $indices;
    }
}
