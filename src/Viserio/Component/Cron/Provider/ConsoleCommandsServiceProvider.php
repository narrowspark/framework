<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Cron\Command\CronListCommand;
use Viserio\Component\Cron\Command\ScheduleRunCommand;

class ConsoleCommandsServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            Application::class => [self::class, 'extendConsole'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'console'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'lazily_commands' => [
                'cron:list' => CronListCommand::class,
                'cron:run'  => ScheduleRunCommand::class,
            ],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Psr\Container\ContainerInterface           $container
     * @param null|\Viserio\Component\Console\Application $console
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function extendConsole(
        ContainerInterface $container,
        ?Application $console = null
    ): ?Application {
        if ($console !== null) {
            // @var Application $console
            $console->addCommands([
                new CronListCommand(),
                new ScheduleRunCommand(),
            ]);
        }

        return $console;
    }
}
