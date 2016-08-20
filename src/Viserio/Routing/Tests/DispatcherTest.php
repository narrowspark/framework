<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Viserio\Routing\Route;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $route = new Route('GET', '/test', null);
    }
}
