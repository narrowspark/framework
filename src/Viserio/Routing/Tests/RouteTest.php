<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Routing\Route;
use Viserio\Routing\RouteParser;
use Viserio\Routing\Router;
use Viserio\Routing\Tests\Fixture\Controller;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testBasicDispatchingOfRoutes()
    {
        $router = $this->getRouter();
        // $router->get('/hello/{name}', function (Request $request, Response $response) {
        //     $name = $request->getAttribute('name');
        //     $response->getBody()->write("Hello, $name");

        //     return $response;
        // });
    }

    public function testGetMethods()
    {
        $route = new Route('GET', '/test', ['uses' => Controller::class . '::string']);

        $this->assertSame(['GET', 'HEAD'], $route->getMethods());

        $route = new Route('PUT', '/test', ['uses' => Controller::class . '::string']);

        $this->assertSame(['PUT'], $route->getMethods());

        $route = new Route(['GET', 'POST'], '/test', ['uses' => Controller::class . '::string']);

        $this->assertSame(['GET', 'POST', 'HEAD'], $route->getMethods());
    }

    public function testGetDomain()
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        $this->assertSame('test.com', $route->getDomain());
    }

    public function testGetAndSetUri()
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        $this->assertSame('/test', $route->getUri());

        $route->setUri('/foo/bar');

        $this->assertSame('/foo/bar', $route->getUri());
    }

    public function testGetAndSetName()
    {
        $route = new Route('GET', '/test', ['as' => 'test']);

        $this->assertSame('test', $route->getName());

        $route->setName('foo');

        $this->assertSame('testfoo', $route->getName());

        $route = new Route('GET', '/test', null);
        $route->setName('test');

        $this->assertSame('test', $route->getName());
    }

    public function testHttpAndHttps()
    {
        $route = new Route('GET', '/test', ['http']);

        $this->assertTrue($route->isHttpOnly());

        $route = new Route('GET', '/test', ['https']);

        $this->assertTrue($route->isHttpsOnly());
    }

    public function testSetAndGetPrefix()
    {
        $route = new Route('GET', '/test', ['prefix' => 'test']);

        $this->assertSame('test', $route->getPrefix());
        $this->assertSame('test/test', $route->getUri());

        $route = new Route('GET', '/test', null);
        $route->addPrefix('foo');

        $this->assertSame('foo/test', $route->getUri());

        $route->addPrefix('test');

        $this->assertSame('test/foo/test', $route->getUri());
    }

    protected function getRouter()
    {
        return new Router($this->mock(ContainerInterface::class), new RouteParser());
    }
}
