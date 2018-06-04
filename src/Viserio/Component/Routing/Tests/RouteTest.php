<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Routing\Pattern;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Tests\Fixture\Controller;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;

/**
 * @internal
 */
final class RouteTest extends TestCase
{
    public function testGetMethods(): void
    {
        $route = new Route('GET', '/test', ['uses' => Controller::class . '@string']);

        $this->assertSame(['GET', 'HEAD'], $route->getMethods());

        $route = new Route('PUT', '/test', ['uses' => Controller::class . '@string']);

        $this->assertSame(['PUT'], $route->getMethods());

        $route = new Route(['GET', 'POST'], '/test', ['controller' => InvokableActionFixture::class]);

        $this->assertSame(['GET', 'POST', 'HEAD'], $route->getMethods());
    }

    public function testGetDomain(): void
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        $this->assertSame('test.com', $route->getDomain());

        $route = new Route('GET', '/test', ['domain' => 'http://test.com']);

        $this->assertSame('test.com', $route->getDomain());

        $route = new Route('GET', '/test', ['domain' => 'https://test.com']);

        $this->assertSame('test.com', $route->getDomain());
    }

    public function testGetAndSetUri(): void
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        $this->assertSame('/test', $route->getUri());
    }

    public function testGetAndSetName(): void
    {
        $route = new Route('GET', '/test', ['as' => 'test']);

        $this->assertSame('test', $route->getName());

        $route->setName('foo');

        $this->assertSame('testfoo', $route->getName());

        $route = new Route('GET', '/test', null);
        $route->setName('test');

        $this->assertSame('test', $route->getName());
    }

    public function testHttpAndHttps(): void
    {
        $route = new Route('GET', '/test', ['http']);

        $this->assertTrue($route->isHttpOnly());

        $route = new Route('GET', '/test', ['https']);

        $this->assertTrue($route->isHttpsOnly());
    }

    public function testSetAndGetPrefix(): void
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

    public function testWhere(): void
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->where(['param1', 'param2'], Pattern::ANY);

        $segments = $route->getSegments();

        $this->assertEquals(new ParameterMatcher('param1', '/^(.+)$/'), $segments[1]);
        $this->assertEquals(new ParameterMatcher('param2', '/^(.+)$/'), $segments[2]);
    }

    public function testParametersFunctions(): void
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->addParameter('test1', 'test1');
        $route->addParameter('test2', 'test2');

        $this->assertTrue($route->hasParameter('test1'));
        $this->assertSame(['test1' => 'test1', 'test2' => 'test2'], $route->getParameters());
        $this->assertSame('test1', $route->getParameter('test1'));

        $route->forgetParameter('test1');

        $this->assertFalse($route->hasParameter('test1'));
    }

    public function testSetAndGetAction(): void
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->setAction([
            'domain'     => 'http://test.com',
            'controller' => 'routeController',
        ]);

        $this->assertSame('test.com', $route->getDomain());
        $this->assertInternalType('array', $route->getAction());
        $this->assertSame('routeController', $route->getActionName());

        $route->setAction([
            'controller' => null,
        ]);

        $this->assertSame('Closure', $route->getActionName());
    }
}
