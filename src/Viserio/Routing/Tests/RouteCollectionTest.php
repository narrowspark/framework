<?php
namespace Viserio\Routing\Tests;

use FastRoute\DataGenerator\GroupCountBased;
use Viserio\Container\Container;
use Viserio\Routing\RouteCollection;
use Viserio\Routing\RouteParser;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Asserts that routes are set via convenience methods.
     */
    public function testSetsRoutesViaConvenienceMethods()
    {
        $router = $this->getRouteCollection();

        $router->get('/route/{wildcard}', 'handler_get', RouteCollection::RESTFUL_STRATEGY);
        $router->post('/route/{wildcard}', 'handler_post', RouteCollection::URI_STRATEGY);
        $router->put('/route/{wildcard}', 'handler_put', RouteCollection::REQUEST_RESPONSE_STRATEGY);
        $router->patch('/route/{wildcard}', 'handler_patch');
        $router->delete('/route/{wildcard}', 'handler_delete');
        $router->head('/route/{wildcard}', 'handler_head');
        $router->options('/route/{wildcard}', 'handler_options');

        $routes = (new \ReflectionClass($router))->getProperty('routes');
        $routes->setAccessible(true);
        $routes = $routes->getValue($router);

        $this->assertCount(7, $routes);

        $this->assertSame($routes['handler_get'], ['strategy' => 1]);
        $this->assertSame($routes['handler_post'], ['strategy' => 2]);
        $this->assertSame($routes['handler_put'], ['strategy' => 0]);
        $this->assertSame($routes['handler_patch'], ['strategy' => 0]);
        $this->assertSame($routes['handler_delete'], ['strategy' => 0]);
        $this->assertSame($routes['handler_head'], ['strategy' => 0]);
        $this->assertSame($routes['handler_options'], ['strategy' => 0]);
    }

    /**
     * Asserts that routes are set via convenience methods with Closures.
     */
    public function testSetsRoutesViaConvenienceMethodsWithClosures()
    {
        $router = $this->getRouteCollection();

        $router->get('/route/{wildcard}', function () {
            return 'get';
        });
        $router->post('/route/{wildcard}', function () {
            return 'post';
        });
        $router->put('/route/{wildcard}', function () {
            return 'put';
        });
        $router->patch('/route/{wildcard}', function () {
            return 'patch';
        });
        $router->delete('/route/{wildcard}', function () {
            return 'delete';
        });
        $router->head('/route/{wildcard}', function () {
            return 'head';
        });
        $router->options('/route/{wildcard}', function () {
            return 'options';
        });
        $router->any('/route/{wildcard}', function () {
            return 'any';
        });

        $routes = (new \ReflectionClass($router))->getProperty('routes');
        $routes->setAccessible(true);
        $routes = $routes->getValue($router);

        $this->assertCount(8, $routes);

        foreach ($routes as $route) {
            $this->assertArrayHasKey('callback', $route);
            $this->assertArrayHasKey('strategy', $route);
        }
    }

    /**
     * Asserts that global strategy is used when set.
     */
    public function testGlobalStrategyIsUsedWhenSet()
    {
        $router = $this->getRouteCollection();

        $router->setStrategy(RouteCollection::URI_STRATEGY);
        $router->get('/route/{wildcard}', 'handler_get', RouteCollection::RESTFUL_STRATEGY);
        $router->post('/route/{wildcard}', 'handler_post', RouteCollection::URI_STRATEGY);
        $router->put('/route/{wildcard}', 'handler_put', RouteCollection::REQUEST_RESPONSE_STRATEGY);
        $router->patch('/route/{wildcard}', 'handler_patch');
        $router->delete('/route/{wildcard}', 'handler_delete');
        $router->head('/route/{wildcard}', 'handler_head');
        $router->options('/route/{wildcard}', 'handler_options');
        $routes = (new \ReflectionClass($router))->getProperty('routes');
        $routes->setAccessible(true);
        $routes = $routes->getValue($router);

        $this->assertCount(7, $routes);

        $this->assertSame($routes['handler_get'], ['strategy' => 2]);
        $this->assertSame($routes['handler_post'], ['strategy' => 2]);
        $this->assertSame($routes['handler_put'], ['strategy' => 2]);
        $this->assertSame($routes['handler_patch'], ['strategy' => 2]);
        $this->assertSame($routes['handler_delete'], ['strategy' => 2]);
        $this->assertSame($routes['handler_head'], ['strategy' => 2]);
        $this->assertSame($routes['handler_options'], ['strategy' => 2]);
    }

    /**
     * Asserts that an exception is thrown when an incorrect strategy type is provided.
     */
    public function testExceptionIsThrownWhenWrongStrategyTypeProvided()
    {
        $this->setExpectedException('InvalidArgumentException');
        $router = $this->getRouteCollection();
        $router->setStrategy('hello');
    }

    /**
     * Asserts that `getDispatcher` method returns correct instance.
     */
    public function testCollectionReturnsDispatcher()
    {
        $router = $this->getRouteCollection();
        $this->assertInstanceOf('Viserio\Routing\Dispatcher', $router->getDispatcher());
        $this->assertInstanceOf('FastRoute\Dispatcher\GroupCountBased', $router->getDispatcher());
    }

    /**
     * Asserts named routes are put in namedRoute array.
     */
    public function testNamedRoutesAreProperlyHandled()
    {
        $router = $this->getRouteCollection();

        $router->addRoute('GET', 'noname', function () {
        });
        $router->addRoute('GET', '@name/named-route', function () {
        });
        $router->addRoute('GET', '@another-name/another-named-route', function () {
        });

        $data = $router->getData();

        $this->assertCount(2, $router->getNamedRoutes());
        $this->assertCount(3, $data);
        $this->assertEquals(['GET', '/', ['handler' => 'handler0', 'name' => 'name']], $data[1]);
        $this->assertEquals(['GET', '/', ['handler' => 'handler0', 'name' => 'another-name']], $data[2]);
    }

    public function testCallableControllers()
    {
        $router = $this->getRouteCollection();

        $router->get('/', new CallableController());

        $routes = (new \ReflectionClass($router))->getProperty('routes');
        $routes->setAccessible(true);
        $routes = $routes->getValue($router);

        $this->assertCount(1, $routes);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNonCallbleObjectControllersError()
    {
        $router = $this->getRouteCollection();

        $router->get('/', new \stdClass());

        $routes = (new \ReflectionClass($router))->getProperty('routes');
        $routes->setAccessible(true);
        $routes = $routes->getValue($router);

        $this->assertCount(0, $routes);
    }

    public function testRedirect()
    {
        $router = $this->getRouteCollection();

        $this->assertSame($router->redirect(), new \Viserio\Routing\Redirect($router));
    }

    private function getRouteCollection()
    {
        return new RouteCollection(
            new Container(),
            new RouteParser(),
            new GroupCountBased()
        );
    }
}

class CallableController
{
    /**
     * @param Symfony\Component\HttpFoundation\Request  $request
     * @param Symfony\Component\HttpFoundation\Response $response
     */
    public function __invoke(
        Symfony\Component\HttpFoundation\Request $request,
        Symfony\Component\HttpFoundation\Response $response
    ) {
    }
}
