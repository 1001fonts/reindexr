<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Logging;

use Maxfonts\Reindexr\Event\ConfigReceivedEvent;
use Maxfonts\Reindexr\Event\IndexClosedEvent;
use Maxfonts\Reindexr\Event\IndicesLoadedEvent;
use Maxfonts\Reindexr\Event\ReindexEvent;
use Maxfonts\Reindexr\Event\TargetIndexCreatedEvent;
use Maxfonts\Reindexr\Event\TargetIndexRollbackEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EventLogger.
 */
final class EventLogger implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    /**
     * EventLogger constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): iterable
    {
        yield ConfigReceivedEvent::class => 'onConfigReceived';
        yield IndicesLoadedEvent::class => 'onIndicesLoaded';
        yield TargetIndexCreatedEvent::class => 'onTargetIndexCreated';
        yield TargetIndexRollbackEvent::class => 'onTargetIndexRollback';
        yield ReindexEvent::class => 'onReindexEvent';
        yield IndexClosedEvent::class => 'onIndexClosed';
    }

    public function onIndexClosed(IndexClosedEvent $event): void
    {
        $this->logger->info(\sprintf('Index "%s" closed', $event->index->getName()));
    }

    public function onReindexEvent(ReindexEvent $event): void
    {
        $settings = $event->settings;
        $this->logger->info(
            \sprintf(
                'Reindex indices %s into %s',
                \implode(', ', $settings->sourceIndices->getKeys()),
                $settings->toIndex
            )
        );
    }

    public function onConfigReceived(ConfigReceivedEvent $event): void
    {
        $this->logger->info('Config received', $event->config->jsonSerialize());
    }

    public function onIndicesLoaded(IndicesLoadedEvent $event): void
    {
        $this->logger->info('Indices loaded', $event->getIndexNames());
    }

    public function onTargetIndexCreated(TargetIndexCreatedEvent $event): void
    {
        $this->logger->info(\sprintf('Target index "%s" created', $event->index->getName()));
    }

    public function onTargetIndexRollback(TargetIndexRollbackEvent $event): void
    {
        $this->logger->info('Rollback target index creation', ['indices' => $event->getNames()]);
    }
}
