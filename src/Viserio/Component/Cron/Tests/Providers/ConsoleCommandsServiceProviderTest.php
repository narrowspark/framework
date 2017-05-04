<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Providers\CacheServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Cron\Commands\CronListCommand;
use Viserio\Component\Cron\Commands\ScheduleRunCommand;
use Viserio\Component\Cron\Providers\ConsoleCommandsServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testConsoleCommands()
    {
        $container = new Container();
        $container->register(new CacheServiceProvider());
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());
        $container->register(new OptionsResolverServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'version' => '1',
                ],
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(CronListCommand::class, $commands['cron:list']);
        self::assertInstanceOf(ScheduleRunCommand::class, $commands['cron:run']);
    }

    public function testConsoleCommandsWithNoConsole()
    {
        $container = new Container();
        $container->register(new ConsoleCommandsServiceProvider());

        self::assertNull($container->get(Application::class));
    }
}
