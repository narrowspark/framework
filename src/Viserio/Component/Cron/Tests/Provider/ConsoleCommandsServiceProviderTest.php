<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Provider\CacheServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Cron\Command\CronListCommand;
use Viserio\Component\Cron\Command\ScheduleRunCommand;
use Viserio\Component\Cron\Provider\ConsoleCommandsServiceProvider;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testConsoleCommands(): void
    {
        $container = new Container();
        $container->register(new CacheServiceProvider());
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        static::assertInstanceOf(CronListCommand::class, $commands['cron:list']);
        static::assertInstanceOf(ScheduleRunCommand::class, $commands['cron:run']);
    }

    public function testConsoleCommandsWithNoConsole(): void
    {
        $container = new Container();
        $container->register(new ConsoleCommandsServiceProvider());

        static::assertNull($container->get(Application::class));
    }

    public function testGetDimensions(): void
    {
        static::assertSame(['viserio', 'console'], ConsoleCommandsServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        static::assertSame(
            [
                'lazily_commands' => [
                    'cron:list' => CronListCommand::class,
                    'cron:run'  => ScheduleRunCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
