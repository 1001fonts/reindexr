<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Alias;

use Maxfonts\Reindexr\Event\IndexClosedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OnIndexClosePurgeAliasesSubscriber.
 */
final class OnIndexClosePurgeAliasesSubscriber implements EventSubscriberInterface
{
    private IndexAwarePurger $purger;

    public function __construct(IndexAwarePurger $purger)
    {
        $this->purger = $purger;
    }

    public static function getSubscribedEvents(): iterable
    {
        yield IndexClosedEvent::class => ['onIndexClosed'];
    }

    public function onIndexClosed(IndexClosedEvent $event): void
    {
        $this->purger->purge($event->index);
    }
}
