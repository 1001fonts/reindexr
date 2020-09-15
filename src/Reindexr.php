<?php
declare(strict_types=1);

namespace Basster\Reindexr;

use Basster\Reindexr\Command\ReindexCommand;
use Basster\Reindexr\ElasticSearch\ClientFactory;
use Basster\Reindexr\ElasticSearch\Handler\CloseIndicesHandler;
use Basster\Reindexr\ElasticSearch\Handler\CreateTargetIndexHandler;
use Basster\Reindexr\ElasticSearch\Handler\ListIndicesHandler;
use Basster\Reindexr\ElasticSearch\Handler\ReindexHandler;
use Basster\Reindexr\ElasticSearch\NewIndicesManager;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Reindexr.
 */
final class Reindexr extends Application
{
    private ContainerInterface $container;

    public function __construct()
    {
        parent::__construct('Reindexr', '0.0.1');
        $this->buildContainer();
        $this->add($this->container->get(ReindexCommand::class));
        $this->setDefaultCommand(ReindexCommand::NAME);
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $this->setDispatcher($eventDispatcher);

        $eventDispatcher->addListener(
            ConsoleEvents::ERROR,
            fn () => $this->container->get(NewIndicesManager::class)->rollback()
        );
    }

    private function buildContainer(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'es.handlers' => [
                \DI\get(ListIndicesHandler::class),
                \DI\get(CreateTargetIndexHandler::class),
                \DI\get(ReindexHandler::class),
                \DI\get(CloseIndicesHandler::class),
            ],
            ReindexCommand::class => \DI\autowire()
                ->constructor(\DI\get(ClientFactory::class), \DI\get('es.handlers')),
            EventDispatcher::class => \DI\autowire(),
            EventDispatcherInterface::class => \DI\create(EventDispatcher::class),
        ]);
        $this->container = $containerBuilder->build();
    }
}
