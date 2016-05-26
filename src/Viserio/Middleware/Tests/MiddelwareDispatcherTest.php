<?php
namespace Viserio\Middleware\Tests;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Viserio\Middleware\Dispatcher;
use Viserio\Middleware\Tests\Fixture\FakeContainerMiddleware;
use Viserio\Middleware\Tests\Fixture\FakeMiddleware;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Middleware\Factory as FactoryContracts;
use Narrowspark\TestingHelper\ArrayContainer;

class MiddelwareDispatcherTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testPipe()
    {
        $request = $this->mock(Request::class);

        $response = $this->mock(Response::class);
        $response->shouldReceive('hasHeader')->with('X-Foo')->andReturn(true);
        $response->shouldReceive('getHeader')->with('X-Foo')->andReturn('modified');
        $response->shouldReceive('getStatusCode')->andReturn(500);
        $response->shouldReceive('withStatus')->andReturnSelf();
        $response->shouldReceive('withAddedHeader')->withAnyArgs()->andReturnSelf();

        $factory = $this->mock(FactoryContracts::class);
        $factory->shouldReceive('createResponse')->andReturn($response);

        $dispatcher = new Dispatcher($factory);

        $dispatcher->pipe(new FakeMiddleware());
        $dispatcher->pipe(function ($request, $frame) {
            $response = $frame->next($request);

            return $response->withStatus(500);
        });

        $default = function($request) use ($factory) {
            // Default to a 404 NOT FOUND response
            return $factory->createResponse(404, [], 'Not Found');
        };

        $response = $dispatcher->run($request, $default);

        $this->assertTrue($response->hasHeader('X-Foo'));
        $this->assertEquals('modified', $response->getHeader('X-Foo'));
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testPipeAddContainer()
    {
        $request = $this->mock(Request::class);

        $response = $this->mock(Response::class);
        $response->shouldReceive('withAddedHeader')->withAnyArgs()->andReturnSelf();
        $response->shouldReceive('getHeader')->with('X-Foo')->andReturn('modified');

        $container = new ArrayContainer(['doo' => 'modified']);

        $factory = $this->mock(FactoryContracts::class);
        $factory->shouldReceive('createResponse')->andReturn($response);

        $dispatcher = new Dispatcher($factory);
        $dispatcher->setContainer($container);
        $dispatcher->pipe(new FakeContainerMiddleware());

        $default = function($request) use ($factory) {
            // Default to a 404 NOT FOUND response
            return $factory->createResponse(404, [], 'Not Found');
        };

        $response = $dispatcher->run($request, $default);

        $this->assertEquals('modified', $response->getHeader('X-Foo'));
    }
}
