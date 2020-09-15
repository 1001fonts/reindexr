<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr;

use DI\ContainerBuilder;
use Maxfonts\Reindexr\Command\ReindexCommand;
use Maxfonts\Reindexr\ElasticSearch\ClientFactory;
use Maxfonts\Reindexr\ElasticSearch\Handler\CloseIndicesHandler;
use Maxfonts\Reindexr\ElasticSearch\Handler\CreateTargetIndexHandler;
use Maxfonts\Reindexr\ElasticSearch\Handler\ListIndicesHandler;
use Maxfonts\Reindexr\ElasticSearch\Handler\ReindexHandler;
use Maxfonts\Reindexr\ElasticSearch\NewIndicesManager;
use Maxfonts\Reindexr\Logging\EventLogger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
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
        $this->initEventDispatcher();
    }

    private function buildContainer(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->addDefinitions([
            'es.handlers' => [
                \DI\get(ListIndicesHandler::class),
                \DI\get(CreateTargetIndexHandler::class),
                \DI\get(ReindexHandler::class),
                \DI\get(CloseIndicesHandler::class),
            ],
            'log.level' => \DI\env('LOG_LEVEL', Logger::INFO),
            ReindexCommand::class => \DI\autowire()
                ->constructor(\DI\get(ClientFactory::class), \DI\get(EventDispatcherInterface::class), \DI\get('es.handlers')),
            EventDispatcher::class => \DI\autowire(),
            EventDispatcherInterface::class => \DI\create(EventDispatcher::class),
            LoggerInterface::class => \DI\factory(function (ContainerInterface $c) {
                $logger = new Logger('reindexr');

                $consoleHandler = new StreamHandler('php://stdout', $c->get('log.level'));
                $consoleHandler->setFormatter(new JsonFormatter());
                $logger->pushHandler($consoleHandler);

                return $logger;
            }),
        ]);
        $this->container = $containerBuilder->build();
    }

    private function initEventDispatcher(): void
    {
        /**
         * @psalm-var \Symfony\Component\EventDispatcher\EventDispatcherInterface
         */
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $this->setDispatcher($eventDispatcher);

        $eventDispatcher->addListener(
            ConsoleEvents::ERROR,
            fn () => $this->container->get(NewIndicesManager::class)->rollback()
        );
        $eventDispatcher->addSubscriber(
            $this->container->get(EventLogger::class)
        );
    }
}
