<?php
declare(strict_types=1);

namespace Basster\Reindexr\Command;

use Basster\Reindexr\ElasticSearch\ClientFactory;
use Basster\Reindexr\ElasticSearch\Handler\AbstractIndicesHandler;
use Basster\Reindexr\ElasticSearch\Handler\CloseIndicesHandler;
use Basster\Reindexr\ElasticSearch\Handler\CreateTargetIndexHandler;
use Basster\Reindexr\ElasticSearch\Handler\ListIndicesHandler;
use Basster\Reindexr\ElasticSearch\Handler\ReindexHandler;
use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReindexCommand.
 */
final class ReindexCommand extends Command
{
    public const NAME = 'reindex';

    private ClientFactory $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        parent::__construct();

        $this->clientFactory = $clientFactory;
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setHelp('Lorem ipsum')
            ->addOption('server', 's', InputOption::VALUE_REQUIRED, 'elasticsearch host', 'localhost')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'elasticsearch port', '9200')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var AbstractIndicesHandler[] $handlers */
        $handlers = [
            new ListIndicesHandler(),
            new CreateTargetIndexHandler(),
            new ReindexHandler(),
            new CloseIndicesHandler(),
        ];

        $client = $this->createESClient($input);

        foreach ($handlers as $index => $handler) {
            $nextIndex = $index + 1;
            $handler->setClient($client);
            if (\array_key_exists($nextIndex, $handlers)) {
                $handler->setNext($handlers[$nextIndex]);
            }
        }

        $chain = $handlers[0];
        $chain->handle(IndexCollection::createEmpty());

        return 0;
    }

    private function createESClient(InputInterface $input): Client
    {
        return $this->clientFactory->create($input->getOption('server'), (int) $input->getOption('port'));
    }
}
