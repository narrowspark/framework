<?php
declare(strict_types=1);
namespace Viserio\Pagination\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\HttpFactory\UriFactory;
use Viserio\Pagination\Adapters\ArrayAdapter;
use Viserio\Pagination\Paginator;

class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

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

        $this->assertSame('http://example.com/test', $pagi->getPath());

        $pagi->setPath('http://example.com/test/');

        $this->assertSame('http://example.com/test', $pagi->getPath());
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

        $this->assertSame('simple', $pagi->getDefaultPresenter());

        $pagi->setDefaultPresenter('foundation5');

        $this->assertSame('foundation5', $pagi->getDefaultPresenter());
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

        $this->assertEquals('/test?page=1', $pagi->getPreviousPageUrl());
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

        $this->assertEquals('/test?page=1', $pagi->getPreviousPageUrl());
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

        $this->assertEquals(2, $pagi->getCurrentPage());
        $this->assertTrue($pagi->hasPages());
        $this->assertTrue($pagi->hasMorePages());
        $this->assertEquals(['item3', 'item4'], $pagi->getItems());
        $this->assertEquals([
            'per_page' => 2, 'current_page' => 2, 'next_page_url' => '/?page=3',
            'prev_page_url' => '/?page=1', 'from' => 3, 'to' => 4, 'data' => ['item3', 'item4'],
        ], $pagi->toArray());
    }

    public function testPaginatorRender()
    {
        $array = new ArrayAdapter(['item3', 'item4', 'item5'], 2);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(11)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        $this->assertSame('<ul class="pagination"><li><a href="/?page=1" rel="prev">&laquo;</a></li><li><a href="/?page=3" rel="next">&raquo;</a></li></ul>', $pagi->render());

        $this->assertSame('<ul class="pagination"><li class="page-item"><a class="page-link" href="/?page=1" rel="prev">&laquo;</a></li><li class="page-item"><a class="page-link" href="/?page=3" rel="next">&raquo;</a></li></ul>', $pagi->render('bootstrap3'));
    }
}
