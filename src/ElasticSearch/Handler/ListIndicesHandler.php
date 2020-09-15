<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch\Handler;

use Basster\Reindexr\ElasticSearch\IndexCollection;
use Basster\Reindexr\Event\IndicesLoadedEvent;

/**
 * Class ListIndicesCommand.
 */
final class ListIndicesHandler extends AbstractIndicesHandler
{
    public function handle(IndexCollection $indices): ?IndexCollection
    {
        $client = $this->getClient();
        $config = $this->getConfig();
        $metadataResponse = $client->request('_cluster/state/metadata/' . $config->prefix . '*');
        $indices = $indices->isEmpty() ? IndexCollection::createFromResponse($metadataResponse, $client) : $indices;

        $this->dispatchEvent(IndicesLoadedEvent::create($indices));

        return parent::handle($indices);
    }
}
