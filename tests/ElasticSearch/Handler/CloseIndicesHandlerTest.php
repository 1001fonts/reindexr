<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\ElasticSearch\Handler;

use Elastica\Index;
use Maxfonts\Reindexr\ElasticSearch\Handler\CloseIndicesHandler;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettings;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettingsFactoryInterface;
use Maxfonts\Reindexr\Input\ReindexConfig;
use Maxfonts\Reindexr\PartitionType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class CloseIndicesHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function closeAllIndicesInTheCollection(): void
    {
        $index = $this->prophesize(Index::class);

        $indices = IndexCollection::createEmpty();
        $indices->add($index->reveal());

        $reindexConfig = ReindexConfig::create('foo', PartitionType::DAILY(), PartitionType::MONTHLY());
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $settingsFactory = $this->prophesize(ReindexSettingsFactoryInterface::class);

        $settingsFactory->generateSettings($indices, $reindexConfig)
            ->shouldBeCalled()
            ->willYield([ReindexSettings::create($indices, 'foo-2020')])
        ;

        $dispatcher->dispatch(Argument::any())
            ->shouldBeCalled()
            ->willReturnArgument(0)
        ;

        $handler = new CloseIndicesHandler($settingsFactory->reveal(), $dispatcher->reveal());
        $handler->setConfig($reindexConfig);
        $handler->handle($indices);

        $index->close()->shouldHaveBeenCalled();
    }
}
