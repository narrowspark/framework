<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Providers;

use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Console\Application;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Console\Providers\ConsoleServiceProvider;
use Viserio\Container\Container;

class ConsoleServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $container->get('config')->set('console', [
            'version' => '1',
        ]);

        $console = $container->get(Application::class);

        $this->assertInstanceOf(Application::class, $console);
        $this->assertSame('1', $console->getVersion());
        $this->assertSame('Cerebro', $console->getName());
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $container->instance('options', [
            'version' => '1',
        ]);

        $this->assertInstanceOf(Application::class, $container->get(Application::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $container->instance('viserio.console.options', [
            'version' => '1',
        ]);

        $this->assertInstanceOf(Application::class, $container->get(Application::class));
    }
}
