<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Basster\Reindexr\ElasticSearch\NewIndicesManager;
use Basster\Reindexr\ElasticSearch\ReindexSettings;
use Basster\Reindexr\ElasticSearch\ReindexSettingsFactoryInterface;
use Elastica\Index;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class CreateTargetIndexHandler.
 */
final class CreateTargetIndexHandler extends AbstractIndicesHandler
{
    private NewIndicesManager $indicesManager;

    public function __construct(ReindexSettingsFactoryInterface $settingsFactory, EventDispatcherInterface $eventDispatcher, NewIndicesManager $indicesManager)
    {
        parent::__construct($settingsFactory, $eventDispatcher);
        $this->indicesManager = $indicesManager;
    }

    public function handle(IndexCollection $indices): ?IndexCollection
    {
        /** @var ReindexSettings $setting */
        foreach ($this->getReindexSettings($indices) as $setting) {
            $sourceIndices = $setting->sourceIndices;
            $index = new Index($this->getClient(), $setting->toIndex);
            $this->createIndex($index, $sourceIndices);

            while (!$index->exists()) {
                \sleep(1);
            }
        }

        return parent::handle($indices);
    }

    /**
     * @throws \JsonException
     */
    private function createIndex(Index $index, IndexCollection $sourceIndices): void
    {
        $index->create([
            'settings' => $sourceIndices->getSettings(),
            'mappings' => $sourceIndices->getMapping(),
            'aliases' => $sourceIndices->getAliases(),
        ]);
        $this->indicesManager->addIndex($index);
    }
}
