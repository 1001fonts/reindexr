<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\ElasticSearch;

use Elastica\Client;
use Elastica\Response;
use Maxfonts\Reindexr\ElasticSearch\Exception\NoIndicesFoundException;
use Maxfonts\Reindexr\ElasticSearch\IndexCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexCollectionTest.
 *
 * @internal
 */
final class IndexCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function firstThrowsNoIndicesFoundExceptionWhenEmpty(): void
    {
        $this->expectException(NoIndicesFoundException::class);

        $response = new Response(['metadata' => ['indices' => []]]);
        $collection = IndexCollection::createFromResponse($response, $this->createMock(Client::class));
        $collection->first();
    }
}
