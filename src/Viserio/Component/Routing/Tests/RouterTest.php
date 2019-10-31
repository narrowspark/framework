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

namespace Viserio\Component\Routing\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Dispatcher\SimpleDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Router;

/**
 * @internal
 *
 * @small
 */
final class RouterTest extends MockeryTestCase
{
    /** @var \Viserio\Contract\Routing\Router */
    protected $router;

    /** @var string */
    private $dir = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Cache';

    protected function setUp(): void
    {
        parent::setUp();

        $dispatcher = new MiddlewareBasedDispatcher();
        $dispatcher->setContainer(\Mockery::mock(ContainerInterface::class));
        $dispatcher->setCachePath($this->dir . \DIRECTORY_SEPARATOR . 'RouterTest.cache');
        $dispatcher->refreshCache(true);

        $router = new Router($dispatcher);
        $router->setContainer(\Mockery::mock(ContainerInterface::class));

        $this->router = $router;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \array_map(static function ($value): void {
            @\unlink($value);
        }, \glob($this->dir . \DIRECTORY_SEPARATOR . '*', \GLOB_NOSORT));

        @\rmdir($this->dir);
    }

    public function testRouterInvalidRouteAction(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $dispatcher = new SimpleDispatcher();
        $dispatcher->setCachePath(__DIR__ . \DIRECTORY_SEPARATOR . 'invalid.cache');

        $router = new Router($dispatcher);

        $router->get('/invalid', ['uses' => stdClass::class]);
        $router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', 'invalid')
        );
    }

    public function testRouterDispatch(): void
    {
        $router = $this->router;

        $router->get('/invalid', function () {
            return \Mockery::mock(ResponseInterface::class);
        });

        self::assertNull($router->getCurrentRoute());

        $router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', '/invalid')
        );

        self::assertInstanceOf(Route::class, $router->getCurrentRoute());
    }

    public function testMergingControllerUses(): void
    {
        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], static function () use ($router): void {
            $router->get('/foo/bar', 'Controller@action');
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('Namespace\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], static function () use ($router): void {
            $router->get('foo/bar', '\\Controller@action');
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], static function () use ($router): void {
            $router->group(['namespace' => 'Nested'], static function () use ($router): void {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('Namespace\\Nested\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], static function () use ($router): void {
            $router->group(['namespace' => '\GlobalScope'], static function () use ($router): void {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('GlobalScope\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['prefix' => 'baz'], static function () use ($router): void {
            $router->group(['namespace' => 'Namespace'], static function () use ($router): void {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[1]->getAction();

        self::assertEquals('Namespace\\Controller@action', $action['controller']);
    }

    public function testRouteGroupingPrefixWithAs(): void
    {
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], static function () use ($router): void {
            $router->get('/bar', ['as' => 'bar', static function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody(
                        (new StreamFactory())
                            ->createStream('Hello')
                    );
            }]);
        });

        self::assertSame([], $router->getGroupStack());

        $route = $router->getRoutes()->getByName('Foo::bar');

        self::assertEquals('/foo/bar', $route->getUri());
    }

    public function testNestedRouteGroupingPrefixWithAs(): void
    {
        // nested with all layers present
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], static function () use ($router): void {
            $router->group(['prefix' => 'bar', 'as' => 'Bar::'], static function () use ($router): void {
                $router->get('baz', ['as' => 'baz', static function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody(
                            (new StreamFactory())
                                ->createStream('Hello')
                        );
                }]);
            });
        });
        $routes = $router->getRoutes();
        $route = $routes->getByName('Foo::Bar::baz');

        self::assertEquals('/foo/bar/baz', $route->getUri());

        // nested with layer skipped
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], static function () use ($router): void {
            $router->group(['prefix' => 'bar'], static function () use ($router): void {
                $router->get('baz', ['as' => 'baz', static function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody(
                            (new StreamFactory())
                                ->createStream('Hello')
                        );
                }]);
            });
        });
        $routes = $router->getRoutes();
        $route = $routes->getByName('Foo::baz');

        self::assertEquals('/foo/bar/baz', $route->getUri());
    }

    public function testRouteGroupingSuffix(): void
    {
        // getSuffix() method
        $router = $this->router;
        $router->group(['suffix' => '.foo'], static function () use ($router): void {
            $router->get('bar', static function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody(
                        (new StreamFactory())
                            ->createStream('Hello')
                    );
            });
        });
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        self::assertEquals('.foo', $routes[0]->getSuffix());
    }

    public function testRouteGroupingSuffixWithAs(): void
    {
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], static function () use ($router): void {
            $router->get('bar', ['as' => 'bar', static function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody(
                        (new StreamFactory())
                            ->createStream('Hello')
                    );
            }]);
        });
        $routes = $router->getRoutes();
        $route = $routes->getByName('Foo::bar');

        self::assertEquals('/bar.foo', $route->getUri());
    }

    public function testNestedRouteGroupingSuffixWithAs(): void
    {
        // nested with all layers present
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], static function () use ($router): void {
            $router->group(['suffix' => '.bar', 'as' => 'Bar::'], static function () use ($router): void {
                $router->get('baz', ['as' => 'baz', static function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody(
                            (new StreamFactory())
                                ->createStream('Hello')
                        );
                }]);
            });
        });
        $routes = $router->getRoutes();
        $route = $routes->getByName('Foo::Bar::baz');

        self::assertEquals('/baz.bar.foo', $route->getUri());

        // nested with layer skipped
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], static function () use ($router): void {
            $router->group(['suffix' => '.bar'], static function () use ($router): void {
                $router->get('baz', ['as' => 'baz', static function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody(
                            (new StreamFactory())
                                ->createStream('Hello')
                        );
                }]);
            });
        });
        $routes = $router->getRoutes();
        $route = $routes->getByName('Foo::baz');

        self::assertEquals('/baz.bar.foo', $route->getUri());
    }

    public function testRouteSuffixing(): void
    {
        $router = $this->router;

        // Suffix route
        $router->get('/foo.bar', static function () {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('Hello')
                );
        });
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();
        $routes[0]->addSuffix('.baz');

        self::assertEquals('/foo.bar.baz', $routes[0]->getUri());

        // Use empty suffix
        $router->get('/foo.bar', static function () {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('Hello')
                );
        });
        $routes = $router->getRoutes()->getRoutes();
        $routes[0]->addSuffix('');

        self::assertEquals('/foo.bar', $routes[0]->getUri());

        // suffix homepage
        $router->get('/', static function () {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('Hello')
                );
        });
        $routes = $router->getRoutes()->getRoutes();
        $routes[1]->addSuffix('bar');

        self::assertEquals('/bar', $routes[1]->getUri());
    }

    public function testSetRemoveAndGetParameters(): void
    {
        $router = $this->router;
        $router->addParameter('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $router->getParameters());

        $router->removeParameter('foo');

        self::assertSame([], $router->getParameters());
    }
}
