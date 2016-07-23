<?php

declare(strict_types=1);
namespace Viserio\Middleware\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Viserio\Middleware\Dispatcher;
use Viserio\Middleware\Tests\Fixture\FakeContainerMiddleware;
use Viserio\Middleware\Tests\Fixture\FakeMiddleware;
use Viserio\Middleware\Tests\Fixture\FakeMiddleware2;

class MiddelwareDispatcherTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testWithMiddleware()
    {
        $request = $this->mock(Request::class);

        $response = $this->mock(Response::class);
        $response->shouldReceive('hasHeader')->with('X-Foo')->andReturn(true);
        $response->shouldReceive('getHeader')->with('X-Foo')->andReturn('modified');
        $response->shouldReceive('getStatusCode')->once()->andReturn(500);
        $response->shouldReceive('withStatus')->once()->andReturnSelf();
        $response->shouldReceive('withAddedHeader')->withAnyArgs()->andReturnSelf();

        $dispatcher = new Dispatcher($response);

        $dispatcher->withMiddleware(new FakeMiddleware());
        $dispatcher->withMiddleware(new FakeMiddleware2());

        $newResponse = $dispatcher->process($request);

        $this->assertTrue($newResponse->hasHeader('X-Foo'));
        $this->assertEquals('modified', $newResponse->getHeader('X-Foo'));
        $this->assertSame(500, $newResponse->getStatusCode());

        $dispatcher->withoutMiddleware(new FakeMiddleware2());
    }

    public function testWithoutMiddleware()
    {
        $request = $this->mock(Request::class);

        $response = $this->mock(Response::class);
        $response->shouldReceive('hasHeader')->with('X-Foo')->andReturn(true);
        $response->shouldReceive('getHeader')->with('X-Foo')->andReturn('modified');
        $response->shouldReceive('withStatus')->never();
        $response->shouldReceive('withAddedHeader')->withAnyArgs()->andReturnSelf();

        $dispatcher = new Dispatcher($response);

        $dispatcher->withMiddleware(new FakeMiddleware());
        $dispatcher->withMiddleware(new FakeMiddleware2());
        $dispatcher->withoutMiddleware(new FakeMiddleware2());

        $newResponse = $dispatcher->process($request);

        $this->assertTrue($newResponse->hasHeader('X-Foo'));
        $this->assertEquals('modified', $newResponse->getHeader('X-Foo'));
    }

    public function testPipeAddContainer()
    {
        $request = $this->mock(Request::class);

        $response = $this->mock(Response::class);
        $response->shouldReceive('withAddedHeader')->withAnyArgs()->andReturnSelf();
        $response->shouldReceive('getHeader')->with('X-Foo')->andReturn('modified');

        $container = new ArrayContainer(['doo' => 'modified']);

        $dispatcher = new Dispatcher($response);
        $dispatcher->setContainer($container);
        $dispatcher->withMiddleware(new FakeContainerMiddleware());

        $newResponse = $dispatcher->process($request);

        $this->assertEquals('modified', $newResponse->getHeader('X-Foo'));
    }
}
