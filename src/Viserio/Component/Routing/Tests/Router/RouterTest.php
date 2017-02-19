<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Interop\Container\ContainerInterface;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Router;

class RouterTest extends TestCase
{
    use MockeryTrait;

    protected $router;

    public function setUp()
    {
        parent::setUp();

        $cachefolder = __DIR__ . '/../Cache/';

        if (! is_dir($cachefolder)) {
            mkdir($cachefolder);
        }

        $router = new Router($this->mock(ContainerInterface::class));
        $router->setCachePath(__DIR__ . '/../Cache/RouterTest.cache');
        $router->refreshCache(true);

        $this->router = $router;
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();

        $this->delTree(__DIR__ . '/../Cache/');
    }

    public function testGroupMerging()
    {
        $old = ['prefix' => 'foo/bar/'];
        self::assertEquals(
            ['prefix' => 'foo/bar/baz', 'suffix' => null, 'namespace' => null, 'where' => []],
            $this->router->mergeGroup(['prefix' => 'baz'], $old)
        );

        $old = ['suffix' => '.bar'];
        self::assertEquals(
            ['prefix' => null, 'suffix' => '.foo.bar', 'namespace' => null, 'where' => []],
            $this->router->mergeGroup(['suffix' => '.foo'], $old)
        );

        $old = ['domain' => 'foo'];
        self::assertEquals(
            ['domain' => 'baz', 'prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => []],
            $this->router->mergeGroup(['domain' => 'baz'], $old)
        );

        $old = ['as' => 'foo.'];
        self::assertEquals(
            ['as' => 'foo.bar', 'prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => []],
            $this->router->mergeGroup(['as' => 'bar'], $old)
        );

        $old = ['where' => ['var1' => 'foo', 'var2' => 'bar']];
        self::assertEquals(
            ['prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => [
                'var1' => 'foo', 'var2' => 'baz', 'var3' => 'qux',
            ]],
            $this->router->mergeGroup(['where' => ['var2' => 'baz', 'var3' => 'qux']], $old)
        );

        $old = [];
        self::assertEquals(
            ['prefix' => null, 'suffix' => null, 'namespace' => null, 'where' => [
                'var1' => 'foo', 'var2' => 'bar',
            ]],
            $this->router->mergeGroup(['where' => ['var1' => 'foo', 'var2' => 'bar']], $old)
        );
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

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
