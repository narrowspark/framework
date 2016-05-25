<?php
namespace Viserio\Middleware\Tests;

use Interop\Container\ContainerInterface;
use Mockery as Mock;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Viserio\Middleware\Dispatcher;
use Viserio\Middleware\Tests\Fixture\FakeContainerMiddleware;
use Viserio\Middleware\Tests\Fixture\FakeMiddleware;

class MiddelwareDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testPipe()
    {
        $request = Mock::mock(Request::class);

        $response = Mock::mock(Response::class);
        $response->shouldReceive('hasHeader')->with('X-Foo')->andReturn(true);
        $response->shouldReceive('getHeader')->with('X-Foo')->andReturn('modified');
        $response->shouldReceive('getStatusCode')->andReturn(500);
        $response->shouldReceive('withStatus')->andReturnSelf();
        $response->shouldReceive('withAddedHeader')->withAnyArgs()->andReturnSelf();

        $dispatcher = new Dispatcher();

        $dispatcher->pipe(new FakeMiddleware());
        $dispatcher->pipe(function ($request, $response, $next) {
            $response = $next($request, $response, $next);

            return $response->withStatus(500);
        });

        $response = $dispatcher(
            $request,
            $response
        );

        $this->assertTrue($response->hasHeader('X-Foo'));
        $this->assertEquals('modified', $response->getHeader('X-Foo'));
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testPipeAddContainer()
    {
        $request = Mock::mock(Request::class);

        $response = Mock::mock(Response::class);
        $response->shouldReceive('withAddedHeader')->withAnyArgs()->andReturnSelf();
        $response->shouldReceive('getHeader')->with('X-Foo')->andReturn('modified');

        $container = Mock::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('doo')->andReturn('modified');

        $dispatcher = new Dispatcher();
        $dispatcher->setContainer($container);
        $dispatcher->pipe(new FakeContainerMiddleware());

        $response = $dispatcher(
            $request,
            $response
        );

        $this->assertEquals('modified', $response->getHeader('X-Foo'));
    }
}
