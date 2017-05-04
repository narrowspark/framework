<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatchers\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Dispatchers\SimpleDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Router;

class RouterTest extends MockeryTestCase
{
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $dispatcher  = new MiddlewareBasedDispatcher();
        $dispatcher->setContainer($this->mock(ContainerInterface::class));
        $dispatcher->setCachePath(__DIR__ . '/../Cache/RouterTest.cache');
        $dispatcher->refreshCache(true);

        $router = new Router($dispatcher);
        $router->setContainer($this->mock(ContainerInterface::class));

        $this->router = $router;
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->delTree(__DIR__ . '/../Cache/');
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testRouterInvalidRouteAction()
    {
        $dispatcher = new SimpleDispatcher();
        $dispatcher->setCachePath(__DIR__ . '/invalid.cache');

        $router = new Router($dispatcher);

        $router->get('/invalid', ['uses' => stdClass::class]);
        $router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', 'invalid')
        );
    }

    public function testRouterDispatch()
    {
        $router = $this->router;

        $router->get('/invalid', function () {
            return $this->mock(ResponseInterface::class);
        });

        self::assertNull($router->getCurrentRoute());

        $router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', '/invalid')
        );

        self::assertInstanceOf(Route::class, $router->getCurrentRoute());
    }

    public function testMergingControllerUses()
    {
        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router) {
            $router->get('/foo/bar', 'Controller@action');
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('Namespace\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router) {
            $router->get('foo/bar', '\\Controller@action');
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router) {
            $router->group(['namespace' => 'Nested'], function () use ($router) {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('Namespace\\Nested\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['namespace' => 'Namespace'], function () use ($router) {
            $router->group(['namespace' => '\GlobalScope'], function () use ($router) {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[0]->getAction();

        self::assertEquals('GlobalScope\\Controller@action', $action['controller']);

        $router = $this->router;
        $router->group(['prefix' => 'baz'], function () use ($router) {
            $router->group(['namespace' => 'Namespace'], function () use ($router) {
                $router->get('foo/bar', 'Controller@action');
            });
        });
        $routes = $router->getRoutes()->getRoutes();
        $action = $routes[1]->getAction();

        self::assertEquals('Namespace\\Controller@action', $action['controller']);
    }

    public function testRouteGroupingPrefixWithAs()
    {
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], function () use ($router) {
            $router->get('/bar', ['as' => 'bar', function () {
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

    public function testNestedRouteGroupingPrefixWithAs()
    {
        /*
         * nested with all layers present
         */
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], function () use ($router) {
            $router->group(['prefix' => 'bar', 'as' => 'Bar::'], function () use ($router) {
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
        self::assertEquals('/foo/bar/baz', $route->getUri());

        /*
         * nested with layer skipped
         */
        $router = $this->router;
        $router->group(['prefix' => 'foo', 'as' => 'Foo::'], function () use ($router) {
            $router->group(['prefix' => 'bar'], function () use ($router) {
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

        self::assertEquals('/foo/bar/baz', $route->getUri());
    }

    public function testRouteGroupingSuffix()
    {
        /*
         * getSuffix() method
         */
        $router = $this->router;
        $router->group(['suffix' => '.foo'], function () use ($router) {
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

        self::assertEquals('.foo', $routes[0]->getSuffix());
    }

    public function testRouteGroupingSuffixWithAs()
    {
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], function () use ($router) {
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

        self::assertEquals('/bar.foo', $route->getUri());
    }

    public function testNestedRouteGroupingSuffixWithAs()
    {
        /*
         * nested with all layers present
         */
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], function () use ($router) {
            $router->group(['suffix' => '.bar', 'as' => 'Bar::'], function () use ($router) {
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

        self::assertEquals('/baz.bar.foo', $route->getUri());

        /*
         * nested with layer skipped
         */
        $router = $this->router;
        $router->group(['suffix' => '.foo', 'as' => 'Foo::'], function () use ($router) {
            $router->group(['suffix' => '.bar'], function () use ($router) {
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

        self::assertEquals('/baz.bar.foo', $route->getUri());
    }

    public function testRouteSuffixing()
    {
        $router = $this->router;

        /*
         * Suffix route
         */
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

        self::assertEquals('/foo.bar.baz', $routes[0]->getUri());

        /*
         * Use empty suffix
         */
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

        self::assertEquals('/foo.bar', $routes[0]->getUri());

        /*
         * suffix homepage
         */
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

        self::assertEquals('/bar', $routes[1]->getUri());
    }

    public function testSetRemoveAndGetParameters()
    {
        $router = $this->router;
        $router->setParameter('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $router->getParameters());

        $router->removeParameter('foo', 'bar');

        self::assertSame([], $router->getParameters());
    }

    private function delTree($dir)
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
