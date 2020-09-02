<?php
declare(strict_types=1);

namespace Basster\Reindexr\Tests\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\Handler\CloseIndicesHandler;
use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Index;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @internal
 * @covers \Basster\Reindexr\ElasticSearch\Handler\CloseIndicesHandler
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

        $handler = new CloseIndicesHandler();
        $handler->handle($indices);

        $index->close()->shouldHaveBeenCalled();
    }
}
