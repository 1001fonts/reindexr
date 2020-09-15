<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Command;

use Elastica\Client;
use Maxfonts\Reindexr\ElasticSearch\ClientFactory;
use Maxfonts\Reindexr\ElasticSearch\Handler\AbstractIndicesHandler;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\Event\ConfigReceivedEvent;
use Maxfonts\Reindexr\Input\ReindexConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ReindexCommand.
 */
final class ReindexCommand extends Command
{
    public const NAME = 'reindex';

    private ClientFactory $clientFactory;
    private array $handlers;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * ReindexCommand constructor.
     *
     * @param AbstractIndicesHandler[] $handlers
     * @psalm-param array<int, AbstractIndicesHandler> $handlers
     */
    public function __construct(ClientFactory $clientFactory, EventDispatcherInterface $eventDispatcher, array $handlers)
    {
        parent::__construct();

        $this->clientFactory = $clientFactory;
        $this->handlers = $handlers;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setHelp('Lorem ipsum')
            ->addArgument('prefix', InputArgument::REQUIRED, 'prefix for indices to manage. May contain wildcards (*)')
            ->addArgument('from', InputArgument::REQUIRED, 'from which partition type to convert (daily|monthly)')
            ->addArgument('to', InputArgument::REQUIRED, 'to which partition type to convert (monthly|yearly)')
            ->addOption('server', 's', InputOption::VALUE_REQUIRED, 'elasticsearch host', 'localhost')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'elasticsearch port', '9200')
            ->addOption('include-current', null, InputOption::VALUE_REQUIRED, 'when `--include-current=false` the current `to-format` (month|year) will be skipped', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->createESClient($input);
        $config = ReindexConfig::createFromInput($input);

        $this->eventDispatcher->dispatch(ConfigReceivedEvent::create($config));

        $chain = null;
        foreach ($this->handlers as $index => $handler) {
            if (0 === $index) {
                $chain = $handler;
            }
            $nextIndex = (int) $index + 1;
            $handler->setConfig($config);
            $handler->setClient($client);
            if (\array_key_exists($nextIndex, $this->handlers)) {
                $handler->setNext($this->handlers[$nextIndex]);
            }
        }

        if ($chain) {
            $chain->handle(IndexCollection::createEmpty());

            return 0;
        }

        return 1;
    }

    private function createESClient(InputInterface $input): Client
    {
        /** @var string $option */
        $option = $input->getOption('server');

        return $this->clientFactory->create($option, (int) $input->getOption('port'));
    }
}
