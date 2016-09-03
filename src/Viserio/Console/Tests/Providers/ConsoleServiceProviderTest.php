<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Providers;

use Viserio\Console\Application;
use Viserio\Console\Providers\ConsoleServiceProvider;
use Viserio\Container\Container;

class ConsoleServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->instance('console.app.version', '1');

        $console = $container->get(Application::class);

        $this->assertInstanceOf(Application::class, $console);
        $this->assertSame('1', $console->getVersion());
        $this->assertSame('Cerebro', $console->getName());
    }
}
