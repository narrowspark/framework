<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Tests\Fixture\Controller;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;
use Viserio\Contract\Routing\Pattern;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class RouteTest extends TestCase
{
    public function testGetMethods(): void
    {
        $route = new Route('GET', '/test', ['uses' => Controller::class . '@string']);

        self::assertSame(['GET', 'HEAD'], $route->getMethods());

        $route = new Route('PUT', '/test', ['uses' => Controller::class . '@string']);

        self::assertSame(['PUT'], $route->getMethods());

        $route = new Route(['GET', 'POST'], '/test', ['controller' => InvokableActionFixture::class]);

        self::assertSame(['GET', 'POST', 'HEAD'], $route->getMethods());
    }

    public function testGetDomain(): void
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        self::assertSame('test.com', $route->getDomain());

        $route = new Route('GET', '/test', ['domain' => 'http://test.com']);

        self::assertSame('test.com', $route->getDomain());

        $route = new Route('GET', '/test', ['domain' => 'https://test.com']);

        self::assertSame('test.com', $route->getDomain());
    }

    public function testGetAndSetUri(): void
    {
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        self::assertSame('/test', $route->getUri());
    }

    public function testGetAndSetName(): void
    {
        $route = new Route('GET', '/test', ['as' => 'test']);

        self::assertSame('test', $route->getName());

        $route->setName('foo');

        self::assertSame('testfoo', $route->getName());

        $route = new Route('GET', '/test', null);
        $route->setName('test');

        self::assertSame('test', $route->getName());
    }

    public function testHttpAndHttps(): void
    {
        $route = new Route('GET', '/test', ['http']);

        self::assertTrue($route->isHttpOnly());

        $route = new Route('GET', '/test', ['https']);

        self::assertTrue($route->isHttpsOnly());
    }

    public function testSetAndGetPrefix(): void
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

    public function testWhere(): void
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->where(['param1', 'param2'], Pattern::ANY);

        $segments = $route->getSegments();

        self::assertEquals(new ParameterMatcher('param1', '/^(.+)$/'), $segments[1]);
        self::assertEquals(new ParameterMatcher('param2', '/^(.+)$/'), $segments[2]);
    }

    public function testParametersFunctions(): void
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);
        $route->addParameter('test1', 'test1');
        $route->addParameter('test2', 'test2');

        self::assertTrue($route->hasParameter('test1'));
        self::assertSame(['test1' => 'test1', 'test2' => 'test2'], $route->getParameters());
        self::assertSame('test1', $route->getParameter('test1'));

        $route->removeParameter('test1');

        self::assertFalse($route->hasParameter('test1'));
    }

    public function testSetAndGetAction(): void
    {
        $route = new Route('GET', '/test/{param1}/{param2}', null);

        $action = [
            'domain' => 'http://test.com',
            'controller' => 'routeController',
        ];
        $route->setAction($action);

        self::assertSame('test.com', $route->getDomain());
        self::assertSame($action, $route->getAction());
        self::assertSame('routeController', $route->getActionName());

        $route->setAction([
            'controller' => null,
        ]);

        self::assertSame('Closure', $route->getActionName());
    }
}
