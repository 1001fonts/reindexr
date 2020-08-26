<?php
declare(strict_types=1);

namespace Basster\Reindexr\ElasticSearch;

use Elastica\Index\Settings;

/**
 * Class IndexSettings.
 */
final class IndexSettings
{
    public int $numberOfShards;
    public int $numberOfReplicas;

    /**
     * IndexSettings constructor.
     */
    private function __construct(int $numberOfShards, int $numberOfReplicas)
    {
        $this->numberOfShards = $numberOfShards;
        $this->numberOfReplicas = $numberOfReplicas;
    }

    public static function create(int $numberOfShards, int $numberOfReplicas): self
    {
        return new self($numberOfShards, $numberOfReplicas);
    }

    public static function fromElasticaSettings(Settings $settings): self
    {
        return new self((int) $settings->getNumberOfShards(), (int) $settings->getNumberOfReplicas());
    }

    public function asArray(): array
    {
        return [
            'number_of_shards' => $this->numberOfShards,
            'number_of_replicas' => $this->numberOfReplicas,
        ];
    }

    public function equals(self $settings): bool
    {
        return empty(\array_diff($this->asArray(), $settings->asArray()));
    }
}
