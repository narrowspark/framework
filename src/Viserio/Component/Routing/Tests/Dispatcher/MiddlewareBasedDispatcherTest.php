<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatchers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;

class MiddlewareBasedDispatcherTest extends AbstractDispatcherTest
{
    public function setUp(): void
    {
        $dispatcher  = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath($this->patch . '/MiddlewareBasedDispatcherTest.cache');
        $dispatcher->refreshCache(true);

        $this->dispatcher = $dispatcher;
    }

    public function testMiddlewareFunc(): void
    {
        $dispatcher = $this->dispatcher;

        $dispatcher->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $dispatcher->getMiddlewares());

        $dispatcher->setMiddlewarePriorities([999 => FooMiddleware::class]);

        self::assertSame([999 => FooMiddleware::class], $dispatcher->getMiddlewarePriorities());
    }

    public function testHandleFound(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route(
            'GET',
            '/test',
            [
                'uses' => function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody((new StreamFactory())->createStream('hello'));
                },
                'middlewares' => 'api',
            ]
        ));

        $this->dispatcher->setMiddlewareGroup('api', [new FakeMiddleware()]);

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertSame('caught', (string) $response->getBody());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Class [Viserio\Component\Routing\Tests\Fixture\FakeMiddleware] is not being managed by the container.
     */
    public function testHandleFoundThrowExceptionClassNotManaged(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route(
            'GET',
            '/test',
            [
                'uses' => function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody((new StreamFactory())->createStream('hello'));
                },
                'middlewares' => FakeMiddleware::class,
            ]
        ));

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->once()
            ->andReturn(false);

        $this->dispatcher->setContainer($container);

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );
    }

    public function testHandleFoundWithResolve(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route(
            'GET',
            '/test',
            [
                'uses' => function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody((new StreamFactory())->createStream('hello'));
                },
                'middlewares' => FakeMiddleware::class,
            ]
        ));

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('has')
            ->once()
            ->andReturn(false);
        $container->shouldReceive('resolve')
            ->once()
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware());

        $this->dispatcher->setContainer($container);

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );
    }
}
