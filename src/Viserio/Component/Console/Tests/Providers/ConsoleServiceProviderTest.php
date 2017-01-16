<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

class ConsoleServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new ConsoleServiceProvider());

        $container->get(RepositoryContract::class)->set('console', [
            'version' => '1',
        ]);

        $console = $container->get(Application::class);

        self::assertInstanceOf(Application::class, $console);
        self::assertSame('1', $console->getVersion());
        self::assertSame('Cerebro', $console->getName());
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());

        $container->instance('options', [
            'version' => '1',
        ]);

        self::assertInstanceOf(Application::class, $container->get(Application::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());

        $container->instance('viserio.console.options', [
            'version' => '1',
        ]);

        self::assertInstanceOf(Application::class, $container->get(Application::class));
    }
}
