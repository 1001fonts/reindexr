<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\ElasticSearch\Handler;

use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use Maxfonts\Reindexr\Event\IndicesLoadedEvent;

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
