<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Provider;

use Cake\Chronos\Chronos;
use Interop\Container\ServiceProviderInterface;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\HttpFoundation\Console\Command\DownCommand;
use Viserio\Component\HttpFoundation\Console\Command\UpCommand;

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
        $commands = [];

        if (\class_exists(Chronos::class) && \class_exists(HttpStatus::class)) {
            $commands = \array_merge(
                $commands,
                [
                    'app:down' => DownCommand::class,
                    'app:up'   => UpCommand::class,
                ]
            );
        }

        return [
            'lazily_commands' => $commands,
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
    public static function extendConsole(ContainerInterface $container, ?Application $console = null): ?Application
    {
        if ($console !== null) {
            $commands = [];

            if (\class_exists(Chronos::class) && \class_exists(HttpStatus::class)) {
                $commands = \array_merge(
                    $commands,
                    [
                        new DownCommand(),
                        new UpCommand(),
                    ]
                );
            }

            $console->addCommands($commands);
        }

        return $console;
    }
}
