<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Cron\Commands\CronListCommand;
use Viserio\Component\Cron\Commands\ForgetCommand;
use Viserio\Component\Cron\Commands\ScheduleRunCommand;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'createConsoleCommands'],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        if ($getPrevious !== null) {
            $console = $getPrevious();

            $console->addCommands([
                new CronListCommand(),
                new ForgetCommand($container->get(CacheItemPoolInterface::class)),
                new ScheduleRunCommand(),
            ]);

            return $console;
        }

        return null;
    }
}
