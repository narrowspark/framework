<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Routing\Tests\Dispatcher;

use Mockery;
use Psr\Container\ContainerInterface;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Support\Invoker;
use Viserio\Contract\Container\CompiledContainer as ContainerContract;
use Viserio\Contract\Routing\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 */
final class MiddlewareBasedDispatcherTest extends AbstractDispatcherTest
{
    /** @var \Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher */
    protected $dispatcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $dispatcher = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath($this->patch . \DIRECTORY_SEPARATOR . 'MiddlewareBasedDispatcherTest.cache');
        $dispatcher->refreshCache(true);

        $this->dispatcher = $dispatcher;
    }

    public function testMiddlewareFunc(): void
    {
        $this->dispatcher->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $this->dispatcher->getMiddleware());

        $this->dispatcher->setMiddlewarePriorities([999 => FooMiddleware::class]);

        self::assertSame([999 => FooMiddleware::class], $this->dispatcher->getMiddlewarePriorities());
    }

    public function testHandleFound(): void
    {
        $collection = new RouteCollection();

        $route = new Route(
            'GET',
            '/test',
            [
                'uses' => static function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody((new StreamFactory())->createStream('hello'));
                },
                'middleware' => 'api',
            ]
        );
        $route->setInvoker(new Invoker());

        $collection->add($route);

        $this->dispatcher->setMiddlewareGroup('api', [new FakeMiddleware()]);

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );

        self::assertSame('caught', (string) $response->getBody());
    }

    public function testHandleFoundThrowExceptionClassNotManaged(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class [Viserio\\Component\\Routing\\Tests\\Fixture\\FakeMiddleware] is not being managed by the container.');

        $collection = new RouteCollection();
        $route = new Route(
            'GET',
            '/test',
            [
                'uses' => static function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody((new StreamFactory())->createStream('hello'));
                },
                'middleware' => FakeMiddleware::class,
            ]
        );
        $route->setInvoker(new Invoker());

        $collection->add($route);

        $container = Mockery::mock(ContainerInterface::class);
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
        $route = new Route(
            'GET',
            '/test',
            [
                'uses' => static function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody((new StreamFactory())->createStream('hello'));
                },
                'middleware' => FakeMiddleware::class,
            ]
        );
        $route->setInvoker(new Invoker());

        $collection->add($route);

        $container = Mockery::mock(ContainerContract::class);
        $container->shouldReceive('has')
            ->once()
            ->andReturn(false);
        $container->shouldReceive('make')
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
