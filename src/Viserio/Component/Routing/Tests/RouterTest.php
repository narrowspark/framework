<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Contract\Routing\Dispatcher;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Dispatcher\SimpleDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Router;

/**
 * @internal
 */
final class RouterTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Contract\Routing\Router
     */
    protected $router;

    /**
     * @var string
     */
    private $dir = __DIR__ . '/../Cache';

    protected function setUp(): void
    {
        parent::setUp();

        $dispatcher = new MiddlewareBasedDispatcher();
        $dispatcher->setContainer($this->mock(ContainerInterface::class));
        $dispatcher->setCachePath($this->dir . '/RouterTest.cache');
        $dispatcher->refreshCache(true);

        $router = new Router($dispatcher);
        $router->setContainer($this->mock(ContainerInterface::class));

        $this->router = $router;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (\is_dir($this->dir)) {
            (new Filesystem())->remove($this->dir);
        }
    }

    public function testMacroable(): void
    {
        Router::macro('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $this->router->foo());
    }

    public function testRouterInvalidRouteAction(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $dispatcher = new SimpleDispatcher();
        $dispatcher->setCachePath(__DIR__ . '/invalid.cache');

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
            return $this->mock(ResponseInterface::class);
        });

        $this->assertNull($router->getCurrentRoute());

        $router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', '/invalid')
        );

        $this->assertInstanceOf(Dispatcher::class, $router->getDispatcher());
        $this->assertInstanceOf(Route::class, $router->getCurrentRoute());
    }

    public function testMergingControllerUses(): void
    {
        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router): void {
            $router->get('/foo/bar', 'Controller@action');
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        $this->assertEquals('Namespace\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router): void {
            $router->get('foo/bar', '\\Controller@action');
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        $this->assertEquals('\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router): void {
            $router->group(['namespace' => 'Nested'], function () use ($router): void {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        $this->assertEquals('Namespace\\Nested\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router): void {
            $router->group(['namespace' => '\GlobalScope'], function () use ($router): void {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        $this->assertEquals('GlobalScope\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['prefix' => 'baz'], function () use ($router): void {
            $router->group(['namespace' => 'Namespace'], function () use ($router): void {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[1]->getAction();

        $this->assertEquals('Namespace\\Controller@action', $action['controller']);
    }

    public function testRouteGroupingPrefixWithAs(): void
    {
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], function () use ($router): void {
            $router->get('/bar', ['as' => 'bar', function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody(
                    (new StreamFactory())
                        ->createStream('Hello')
                );
            }]);
        });

        $this->assertSame([], $router->getGroupStack());

        $route = $router->getRoutes()->getByName('Foo::bar');

        $this->assertEquals('/foo/bar', $route->getUri());
    }

    public function testNestedRouteGroupingPrefixWithAs(): void
    {
        // nested with all layers present
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], function () use ($router): void {
            $router->group(['prefix' => 'bar', 'as' => 'Bar::'], function () use ($router): void {
                $router->get('baz', ['as' => 'baz', function () {
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
        $route  = $routes->getByName('Foo::Bar::baz');
        $this->assertEquals('/foo/bar/baz', $route->getUri());

        // nested with layer skipped
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], function () use ($router): void {
            $router->group(['prefix' => 'bar'], function () use ($router): void {
                $router->get('baz', ['as' => 'baz', function () {
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
        $route  = $routes->getByName('Foo::baz');

        $this->assertEquals('/foo/bar/baz', $route->getUri());
    }

    public function testRouteGroupingSuffix(): void
    {
        // getSuffix() method
        $router = $this->router;
        $router->group(['suffix' => '.foo'], function () use ($router): void {
            $router->get('bar', function () {
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

        $this->assertEquals('.foo', $routes[0]->getSuffix());
    }

    public function testRouteGroupingSuffixWithAs(): void
    {
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], function () use ($router): void {
            $router->get('bar', ['as' => 'bar', function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody(
                        (new StreamFactory())
                            ->createStream('Hello')
                    );
            }]);
        });
        $routes = $router->getRoutes();
        $route  = $routes->getByName('Foo::bar');

        $this->assertEquals('/bar.foo', $route->getUri());
    }

    public function testNestedRouteGroupingSuffixWithAs(): void
    {
        // nested with all layers present
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], function () use ($router): void {
            $router->group(['suffix' => '.bar', 'as' => 'Bar::'], function () use ($router): void {
                $router->get('baz', ['as' => 'baz', function () {
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
        $route  = $routes->getByName('Foo::Bar::baz');

        $this->assertEquals('/baz.bar.foo', $route->getUri());

        // nested with layer skipped
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], function () use ($router): void {
            $router->group(['suffix' => '.bar'], function () use ($router): void {
                $router->get('baz', ['as' => 'baz', function () {
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
        $route  = $routes->getByName('Foo::baz');

        $this->assertEquals('/baz.bar.foo', $route->getUri());
    }

    public function testRouteSuffixing(): void
    {
        $router = $this->router;

        // Suffix route
        $router->get('/foo.bar', function () {
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

        $this->assertEquals('/foo.bar.baz', $routes[0]->getUri());

        // Use empty suffix
        $router->get('/foo.bar', function () {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('Hello')
                );
        });
        $routes = $router->getRoutes()->getRoutes();
        $routes[0]->addSuffix('');

        $this->assertEquals('/foo.bar', $routes[0]->getUri());

        // suffix homepage
        $router->get('/', function () {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('Hello')
                );
        });
        $routes = $router->getRoutes()->getRoutes();
        $routes[1]->addSuffix('bar');

        $this->assertEquals('/bar', $routes[1]->getUri());
    }

    public function testSetRemoveAndGetParameters(): void
    {
        $router = $this->router;
        $router->addParameter('foo', 'bar');

        $this->assertSame(['foo' => 'bar'], $router->getParameters());

        $router->removeParameter('foo');

        $this->assertSame([], $router->getParameters());
    }
}
