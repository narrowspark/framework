<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFactory\UriFactory;
use Viserio\Component\Pagination\Adapter\ArrayAdapter;
use Viserio\Component\Pagination\Adapter\NullAdapter;
use Viserio\Component\Pagination\Paginator;

/**
 * @internal
 */
final class PaginatorTest extends MockeryTestCase
{
    public function testToJson(): void
    {
        $array = new ArrayAdapter(['item1', 'item2', 'item3'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(5)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/test'));

        $pagi = new Paginator($array, $request);

        static::assertJson($pagi->toJson());
    }

    public function testJsonSerialize(): void
    {
        $array = new ArrayAdapter(['item1', 'item2', 'item3'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(5)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/test'));

        $pagi = new Paginator($array, $request);

        static::assertInternalType('array', $pagi->jsonSerialize());
    }

    public function testSetAndGetPath(): void
    {
        $array = new ArrayAdapter(['item1', 'item2', 'item3'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->once()
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/test'));

        $pagi = new Paginator($array, $request);

        $pagi->setPath('http://example.com/test');

        static::assertSame('http://example.com/test', $pagi->getPath());

        $pagi->setPath('http://example.com/test/');

        static::assertSame('http://example.com/test', $pagi->getPath());
    }

    public function testSetAndGetDefaultPresenter(): void
    {
        $array = new ArrayAdapter(['item1', 'item2', 'item3'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->once()
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/test'));

        $pagi = new Paginator($array, $request);

        static::assertSame('simple', $pagi->getDefaultPresenter());

        $pagi->setDefaultPresenter('foundation5');

        static::assertSame('foundation5', $pagi->getDefaultPresenter());
    }

    public function testPaginatorGeneratesUrlsWithoutTrailingSlash(): void
    {
        $array = new ArrayAdapter(['item1', 'item2', 'item3'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(3)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/test'));

        $pagi = new Paginator($array, $request);

        static::assertEquals('/test?page=1', $pagi->getPreviousPageUrl());
    }

    public function testPaginatorRemovesTrailingSlashes(): void
    {
        $array = new ArrayAdapter(['item1', 'item2', 'item3'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(3)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/test/'));

        $pagi = new Paginator($array, $request);

        static::assertEquals('/test?page=1', $pagi->getPreviousPageUrl());
    }

    public function testSimplePaginatorReturnsRelevantContextInformation(): void
    {
        $array = new ArrayAdapter(['item3', 'item4', 'item5'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(7)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        static::assertEquals(2, $pagi->getCurrentPage());
        static::assertTrue($pagi->hasPages());
        static::assertTrue($pagi->hasMorePages());
        static::assertEquals(['item3', 'item4'], $pagi->getItems());
        static::assertEquals([
            'per_page'      => 2, 'current_page' => 2, 'next_page_url' => '/?page=3',
            'prev_page_url' => '/?page=1', 'from' => 3, 'to' => 4, 'data' => ['item3', 'item4'], 'path' => '/',
        ], $pagi->toArray());
    }

    public function testPaginatorWithNullAdapter(): void
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')
            ->times(6)
            ->andReturn([]);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator(new NullAdapter(), $request);

        static::assertEquals(1, $pagi->getCurrentPage());
        static::assertFalse($pagi->hasPages());
        static::assertFalse($pagi->hasMorePages());
        static::assertEquals([], $pagi->getItems());
        static::assertEquals([
            'per_page'      => 0, 'current_page' => 1, 'next_page_url' => null,
            'prev_page_url' => null, 'from' => 0, 'to' => 0, 'data' => [], 'path' => '/',
        ], $pagi->toArray());

        static::assertSame('', (string) $pagi);
    }
}
