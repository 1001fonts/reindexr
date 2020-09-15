<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Handler;

use Elastica\Request;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\ElasticSearch\ReindexSettings;
use Maxfonts\Reindexr\Event\ReindexEvent;

/**
 * Class ReindexHandler.
 */
final class ReindexHandler extends AbstractIndicesHandler
{
    public function handle(IndexCollection $indices): ?IndexCollection
    {
        /** @var ReindexSettings $setting */
        foreach ($this->getReindexSettings($indices) as $setting) {
            $this->reindex($setting);
        }

        return parent::handle($indices);
    }

    private function reindex(ReindexSettings $setting): void
    {
        $this->dispatchEvent(ReindexEvent::create($setting));
        $this->getClient()->request('_reindex?wait_for_completion=true', Request::POST, [
            'source' => [
                'index' => $setting->sourceIndices->getKeys(),
            ],
            'dest' => [
                'index' => $setting->toIndex,
            ],
        ]);
    }
}
