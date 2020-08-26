<?php
declare(strict_types=1);

namespace Basster\Reindexr\Command;

use Basster\Reindexr\ElasticSearch\ListIndicesCommand;
use Elastica\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReindexCommand.
 */
final class ReindexCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('reindex')
            ->setHelp('Lorem ipsum')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = new Client(['host' => 'localhost', 'port' => 9200]);
        $command = new ListIndicesCommand();
        $command->run($client);

        return 0;
    }
}
