<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\ElasticSearch\Alias;

use Elastica\Client;
use Elastica\Index;
use Maxfonts\Reindexr\ElasticSearch\Alias\IndexAwarePurger;
use Maxfonts\Reindexr\ElasticSearch\Alias\OnIndexClosePurgeAliasesSubscriber;
use Maxfonts\Reindexr\Event\IndexClosedEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @internal
 */
final class OnIndexClosePurgeAliasesSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function subscribesIndexClosedEvent(): void
    {
        $events = \iterator_to_array(OnIndexClosePurgeAliasesSubscriber::getSubscribedEvents());
        $eventNames = \array_keys($events);

        self::assertContains(IndexClosedEvent::class, $eventNames);
    }

    /**
     * @test
     */
    public function delegateIndexFromClosedEventToAliasPurger(): void
    {
        $aliasPurger = $this->prophesize(IndexAwarePurger::class);
        $subscriber = new OnIndexClosePurgeAliasesSubscriber($aliasPurger->reveal());

        $index = new Index($this->createMock(Client::class), 'some-index');
        $subscriber->onIndexClosed(IndexClosedEvent::create($index));

        $aliasPurger->purge($index)->shouldHaveBeenCalled();
    }
}
