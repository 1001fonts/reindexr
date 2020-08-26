<?php
declare(strict_types=1);

namespace Basster\Reindexr\Tests\ElasticSearch;

use Basster\Reindexr\ElasticSearch\Exception\NoIndicesFoundException;
use Basster\Reindexr\ElasticSearch\IndexCollection;
use Elastica\Client;
use Elastica\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexCollectionTest.
 *
 * @covers \IndexCollection
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
