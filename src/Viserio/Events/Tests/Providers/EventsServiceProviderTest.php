<?php
declare(strict_types=1);
namespace Viserio\Events\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Events\Dispatcher;

class EventsServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());

        $this->assertInstanceOf(Dispatcher::class, $container->get(Dispatcher::class));
    }
}
