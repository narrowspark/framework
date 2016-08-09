<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Viserio\Routing\{
    Dispatcher,
    Route,
    RouteCollection
};

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $route = new Route('GET', 'test', null);

        $collection = new RouteCollection();
        $collection->addRoute($route);

        $dispatcher = new Dispatcher($collection);

        var_dump($dispatcher->match('GET', 'test'));
    }
}
