<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Routing\Commands\RouteListCommand;
use Viserio\Component\Routing\Providers\ConsoleCommandsServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'version' => '1',
                ],
                'routing' => [
                    'path' => '',
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(RouteListCommand::class, $commands['route:table']);
    }
}
