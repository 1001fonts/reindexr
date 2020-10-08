<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\ElasticSearch\Alias;

use Elastica\Index;
use Maxfonts\Reindexr\ElasticSearch\Alias\AliasPurger;
use Maxfonts\Reindexr\ElasticSearch\Alias\IndexAwarePurger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @internal
 */
final class AliasPurgerTest extends TestCase
{
    use ProphecyTrait;

    private AliasPurger $purger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->purger = new AliasPurger();
    }

    /**
     * @test
     */
    public function aliasPurgerIsAnIndexAwarePurger(): void
    {
        self::assertInstanceOf(IndexAwarePurger::class, $this->purger);
    }

    /**
     * @test
     */
    public function purgeRemovesAllAliasesFromAnIndex(): void
    {
        $index = $this->prophesize(Index::class);

        $this->purger->purge($index->reveal());

        $index->removeAlias('*')
            ->shouldHaveBeenCalled()
        ;
    }
}
