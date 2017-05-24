<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFactory\UriFactory;
use Viserio\Component\Pagination\Adapters\ArrayAdapter;
use Viserio\Component\Pagination\Adapters\NullAdapter;
use Viserio\Component\Pagination\Paginator;

class PaginatorTest extends MockeryTestCase
{
    public function testToJson()
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

        self::assertJson($pagi->toJson());
    }

    public function testJsonSerialize()
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

        self::assertTrue(is_array($pagi->jsonSerialize()));
    }

    public function testSetAndGetPath()
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

        self::assertSame('http://example.com/test', $pagi->getPath());

        $pagi->setPath('http://example.com/test/');

        self::assertSame('http://example.com/test', $pagi->getPath());
    }

    public function testSetAndGetDefaultPresenter()
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

        self::assertSame('simple', $pagi->getDefaultPresenter());

        $pagi->setDefaultPresenter('foundation5');

        self::assertSame('foundation5', $pagi->getDefaultPresenter());
    }

    public function testPaginatorGeneratesUrlsWithoutTrailingSlash()
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

        self::assertEquals('/test?page=1', $pagi->getPreviousPageUrl());
    }

    public function testPaginatorRemovesTrailingSlashes()
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

        self::assertEquals('/test?page=1', $pagi->getPreviousPageUrl());
    }

    public function testSimplePaginatorReturnsRelevantContextInformation()
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

        self::assertEquals(2, $pagi->getCurrentPage());
        self::assertTrue($pagi->hasPages());
        self::assertTrue($pagi->hasMorePages());
        self::assertEquals(['item3', 'item4'], $pagi->getItems());
        self::assertEquals([
            'per_page'      => 2, 'current_page' => 2, 'next_page_url' => '/?page=3',
            'prev_page_url' => '/?page=1', 'from' => 3, 'to' => 4, 'data' => ['item3', 'item4'], 'path' => '/',
        ], $pagi->toArray());
    }

    public function testPaginatorWithNullAdapter()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')
            ->times(6)
            ->andReturn([]);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator(new NullAdapter(), $request);

        self::assertEquals(1, $pagi->getCurrentPage());
        self::assertFalse($pagi->hasPages());
        self::assertFalse($pagi->hasMorePages());
        self::assertEquals([], $pagi->getItems());
        self::assertEquals([
            'per_page'      => 0, 'current_page' => 1, 'next_page_url' => null,
            'prev_page_url' => null, 'from' => 0, 'to' => 0, 'data' => [], 'path' => '/',
        ], $pagi->toArray());

        self::assertSame('', (string) $pagi);
    }
}
