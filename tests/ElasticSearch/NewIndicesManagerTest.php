<?php
declare(strict_types=1);

namespace Basster\Reindexr\Tests\ElasticSearch;

use Basster\Reindexr\ElasticSearch\NewIndicesManager;
use Basster\Reindexr\Event\TargetIndexCreatedEvent;
use Basster\Reindexr\Event\TargetIndexRollbackEvent;
use Elastica\Index;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class NewIndicesManagerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $dispatcher;
    private NewIndicesManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->manager = new NewIndicesManager($this->dispatcher->reveal());
        $this->dispatcher->dispatch(Argument::any())
            ->willReturnArgument(0)
        ;
    }

    /**
     * @test
     */
    public function addIndexDispatchesTargetIndexCreatedEvent(): void
    {
        $index = $this->prophesize(Index::class)->reveal();

        $this->dispatcher->dispatch(Argument::that(function (TargetIndexCreatedEvent $event) use ($index) {
            self::assertSame($index, $event->index);

            return true;
        }))
            ->shouldBeCalled()
            ->willReturnArgument(0)
            ;

        $this->manager->addIndex($index);
    }

    /**
     * @test
     */
    public function rollbackDeletesAllGivenIndices(): void
    {
        $index1 = $this->prophesize(Index::class);
        $index2 = $this->prophesize(Index::class);

        $indices = [
            $index1,
            $index2,
        ];

        foreach ($indices as $index) {
            $this->manager->addIndex($index->reveal());

            $index->delete()
                ->shouldBeCalled()
            ;
        }

        $this->manager->rollback();
    }

    /**
     * @test
     */
    public function rollbackDispatchesTargetIndexRollbackEvent(): void
    {
        $indexName = 'my-index';

        $index = $this->prophesize(Index::class);
        $this->manager->addIndex($index->reveal());

        $index->getName()
            ->shouldBeCalled()
            ->willReturn($indexName)
        ;
        $index->delete()
            ->shouldBeCalled()
        ;

        $this->dispatcher->dispatch(Argument::that(function (TargetIndexRollbackEvent $event) use ($indexName) {
            self::assertSame([$indexName], $event->getNames());

            return true;
        }));

        $this->manager->rollback();
    }
}
