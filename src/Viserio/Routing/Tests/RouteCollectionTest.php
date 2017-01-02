<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Routing\Route;
use Viserio\Routing\RouteCollection;

class RouteCollectionTest extends TestCase
{
    public function testGet()
    {
        $collection = new RouteCollection();
        $route      = new Route('GET', '/test', ['domain' => 'test.com']);

        self::assertInstanceOf(Route::class, $collection->add($route));
    }

    public function testMatch()
    {
        $collection = new RouteCollection();
        $collection->add($route1 = new Route('GET', '/test', null));
        $collection->add($route2 = new Route('PATCH', '/test2', null));
        $collection->add($route3 = new Route(['GET', 'POST'], '/test', null));

        self::assertSame($route2, $collection->match('PATCH/test2'));
        self::assertSame($route1, $collection->match('GET|HEAD/test'));
        self::assertSame($route3, $collection->match('GET|POST|HEAD/test'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Route not found, looks like your route cache is stale.
     */
    public function testMatchToThrowException()
    {
        $collection = new RouteCollection();
        $collection->match('PATCH/test2');
    }

    public function testHasNamedRoute()
    {
        $collection = new RouteCollection();
        $collection->add(new Route('GET', '/test', ['as' => 'narrowspark']));

        self::assertTrue($collection->hasNamedRoute('narrowspark'));
        self::assertFalse($collection->hasNamedRoute('PATCH/test2'));
    }

    public function testGetByName()
    {
        $collection = new RouteCollection();
        $collection->add($route = new Route('GET', '/test', ['as' => 'narrowspark']));

        self::assertSame($route, $collection->getByName('narrowspark'));
        self::assertNull($collection->getByName('PATCH/test2'));
    }

    public function testGetByAction()
    {
        $collection = new RouteCollection();
        $collection->add($route = new Route('GET', '/test', ['controller' => 'narrowspark']));

        self::assertSame($route, $collection->getByAction('narrowspark'));
        self::assertNull($collection->getByAction('PATCH/test2'));
    }
}
