<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Viserio\Routing\Dispatcher;
use Viserio\Routing\Route;
use Viserio\Routing\RouteCollection;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $route = new Route('GET', 'test', null);

        $collection = new RouteCollection();
        $collection->addRoute($route);

        $dispatcher = new Dispatcher($collection);
    }
}
