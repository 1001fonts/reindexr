<?php
declare(strict_types=1);

namespace Basster\Reindexr;

use Basster\Reindexr\Command\ReindexCommand;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

/**
 * Class Reindexr.
 */
final class Reindexr extends Application
{
    private ContainerInterface $container;

    public function __construct()
    {
        parent::__construct('Reindexr', '0.0.1');
        $this->initContainer();
        $this->add($this->container->get(ReindexCommand::class));
        $this->setDefaultCommand(ReindexCommand::NAME);
    }

    private function initContainer(): void
    {
        $containerBuilder = new ContainerBuilder();
        $this->container = $containerBuilder->build();
    }
}
