<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Routing\Command\RouteListCommand;
use Viserio\Component\Routing\Provider\ConsoleCommandsServiceProvider;
use Viserio\Component\Routing\Provider\RoutingServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'routing' => [
                    'path' => '',
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(RouteListCommand::class, $commands['route:table']);
    }

    public function testGetDimensions(): void
    {
        self::assertSame(['viserio', 'console'], ConsoleCommandsServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        self::assertSame(
            [
                'lazily_commands' => [
                    'route:table' => RouteListCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
