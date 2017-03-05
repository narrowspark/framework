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
            'prev_page_url' => '/?page=1', 'from' => 3, 'to' => 4, 'data' => ['item3', 'item4'],
        ], $pagi->toArray());
    }

    public function testPaginatorRenderSimplePagination()
    {
        $array = new ArrayAdapter(['item3', 'item4', 'item5'], 2);

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')
            ->times(4)
            ->andReturn(['page' => '1']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        self::assertSame('<ul class="pagination"><li>&laquo;</li><li><a href="/?page=2" rel="next">&raquo;</a></li></ul>', (string) $pagi);

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')
            ->times(6)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        self::assertSame('<ul class="pagination"><li><a href="/?page=1" rel="prev">&laquo;</a></li><li><a href="/?page=3" rel="next">&raquo;</a></li></ul>', $pagi->render());

        $array = new ArrayAdapter(['item3', 'item4', 'item5'], 3);

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')
            ->times(5)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        self::assertSame('<ul class="pagination"><li><a href="/?page=1" rel="prev">&laquo;</a></li><li>&raquo;</li></ul>', $pagi->render());
    }

    public function testPaginatorRenderBootstrap()
    {
        $array = new ArrayAdapter(['item3', 'item4', 'item5'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(6)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        self::assertSame('<ul class="pagination"><li class="page-item"><a class="page-link" href="/?page=1" rel="prev">&laquo;</a></li><li class="page-item"><a class="page-link" href="/?page=3" rel="next">&raquo;</a></li></ul>', $pagi->render('bootstrap4'));
    }

    public function testPaginatorRenderFoundation6()
    {
        $array = new ArrayAdapter(['item3', 'item4', 'item5'], 2);

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getQueryParams')
            ->times(6)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        self::assertSame('<ul class="pagination" role="navigation"><li class="pagination-previous"><a href="/?page=1" rel="prev">&laquo;</a></li><li class="pagination-next"><a href="/?page=3" rel="next">&raquo;</a></li></ul>', $pagi->render('foundation6'));
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
            'prev_page_url' => null, 'from' => 0, 'to' => 0, 'data' => [],
        ], $pagi->toArray());

        self::assertSame('', (string) $pagi);
    }
}
