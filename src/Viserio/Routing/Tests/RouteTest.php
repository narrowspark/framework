<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Routing\Pattern;
use Viserio\Routing\Route;
use Viserio\Routing\Segments\ParameterSegment;
use Viserio\Routing\Tests\Fixture\Controller;

class RouteTest extends TestCase
{
    public function testGetMethods()
    {
        $route = new Route('GET', '/test', ['uses' => Controller::class . '@string']);

        self::assertSame(['GET', 'HEAD'], $route->getMethods());

        $route = new Route('PUT', '/test', ['uses' => Controller::class . '@string']);

        self::assertSame(['PUT'], $route->getMethods());

        $route = new Route(['GET', 'POST'], '/test', ['uses' => Controller::class . '@string']);

        self::assertSame(['GET', 'POST', 'HEAD'], $route->getMethods());
    }

    public function testGetDomain()
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        self::assertSame('test.com', $route->getDomain());

        $route = new Route('GET', '/test', ['domain' => 'http://test.com']);

        self::assertSame('test.com', $route->getDomain());

        $route = new Route('GET', '/test', ['domain' => 'https://test.com']);

        self::assertSame('test.com', $route->getDomain());
    }

    public function testGetAndSetUri()
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        self::assertSame('/test', $route->getUri());
    }

    public function testGetAndSetName()
    {
        $route = new Route('GET', '/test', ['as' => 'test']);

        self::assertSame('test', $route->getName());

        $route->setName('foo');

        self::assertSame('testfoo', $route->getName());

        $route = new Route('GET', '/test', null);
        $route->setName('test');

        self::assertSame('test', $route->getName());
    }

    public function testHttpAndHttps()
    {
        $route = new Route('GET', '/test', ['http']);

        self::assertTrue($route->isHttpOnly());

        $route = new Route('GET', '/test', ['https']);

        self::assertTrue($route->isHttpsOnly());
    }

    public function testSetAndGetPrefix()
    {
        $route = new Route('GET', '/test', ['prefix' => 'test']);

        self::assertSame('test', $route->getPrefix());
        self::assertSame('test/test', $route->getUri());

        $route = new Route('GET', '/test', null);
        $route->addPrefix('foo');

        self::assertSame('foo/test', $route->getUri());

        $route->addPrefix('test');

        self::assertSame('test/foo/test', $route->getUri());
    }

    public function testWhere()
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->where(['param1', 'param2'], Pattern::ANY);

        $segments = $route->getSegments();

        self::assertEquals(new ParameterSegment('param1', '/^(.+)$/'), $segments[1]);
        self::assertEquals(new ParameterSegment('param2', '/^(.+)$/'), $segments[2]);
    }

    public function testParametersFunctions()
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->setParameter('test1', 'test1');
        $route->setParameter('test2', 'test2');

        self::assertTrue($route->hasParameters());
        self::assertTrue($route->hasParameter('test1'));
        self::assertSame(['test1' => 'test1', 'test2' => 'test2'], $route->getParameters());
        self::assertSame('test1', $route->getParameter('test1'));

        $route->forgetParameter('test1');

        self::assertFalse($route->hasParameter('test1'));
    }

    public function testSetAndGetAction()
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->setAction([
            'domain'     => 'http://test.com',
            'controller' => 'routeController',
        ]);

        self::assertSame('test.com', $route->getDomain());
        self::assertTrue(is_array($route->getAction()));
        self::assertSame('routeController', $route->getActionName());

        $route->setAction([
            'controller' => null,
        ]);

        self::assertSame('Closure', $route->getActionName());
    }
}
