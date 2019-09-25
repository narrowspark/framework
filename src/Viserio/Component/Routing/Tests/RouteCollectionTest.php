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

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;

/**
 * @internal
 *
 * @small
 */
final class RouteCollectionTest extends TestCase
{
    public function testGet(): void
    {
        $collection = new RouteCollection();
        $route = new Route('GET', '/test', ['domain' => 'test.com']);

        self::assertInstanceOf(Route::class, $collection->add($route));
    }

    public function testMatch(): void
    {
        $collection = new RouteCollection();
        $collection->add($route1 = new Route('GET', '/test', null));
        $collection->add($route2 = new Route('PATCH', '/test2', null));
        $collection->add($route3 = new Route(['GET', 'POST'], '/test', null));

        self::assertSame($route2, $collection->match('PATCH/test2'));
        self::assertSame($route1, $collection->match('GET|HEAD/test'));
        self::assertSame($route3, $collection->match('GET|POST|HEAD/test'));
    }

    public function testMatchToThrowException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Route not found, looks like your route cache is stale.');

        $collection = new RouteCollection();
        $collection->match('PATCH/test2');
    }

    public function testHasNamedRoute(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route('GET', '/test', ['as' => 'narrowspark']));

        self::assertTrue($collection->hasNamedRoute('narrowspark'));
        self::assertFalse($collection->hasNamedRoute('PATCH/test2'));
    }

    public function testGetByName(): void
    {
        $collection = new RouteCollection();
        $collection->add($route = new Route('GET', '/test', ['as' => 'narrowspark']));

        self::assertSame($route, $collection->getByName('narrowspark'));
        self::assertNull($collection->getByName('PATCH/test2'));
    }

    public function testGetByAction(): void
    {
        $collection = new RouteCollection();
        $collection->add($route = new Route('GET', '/test', ['controller' => 'narrowspark']));

        self::assertSame($route, $collection->getByAction('narrowspark'));
        self::assertNull($collection->getByAction('PATCH/test2'));
    }
}
