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
}
