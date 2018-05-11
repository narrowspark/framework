<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Process\Process;
use Viserio\Component\Config\Command\ConfigCacheCommand as BaseConfigCacheCommand;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Foundation\Config\Command\ConfigCacheCommand;
use Viserio\Component\Foundation\Config\Command\ConfigClearCommand;
use Viserio\Component\Foundation\Console\Command\DownCommand;
use Viserio\Component\Foundation\Console\Command\KeyGenerateCommand;
use Viserio\Component\Foundation\Console\Command\ServeCommand;
use Viserio\Component\Foundation\Console\Command\UpCommand;

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
    public static function getDimensions(): iterable
    {
        return ['viserio', 'console'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        $commands = [
            'app:down' => DownCommand::class,
            'app:up'   => UpCommand::class,
        ];

        if (\class_exists(BaseConfigCacheCommand::class)) {
            $commands = \array_merge(
                $commands,
                [
                    'config:cache' => ConfigCacheCommand::class,
                    'config:clear' => ConfigClearCommand::class,
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
    public static function extendConsole(
        ContainerInterface $container,
        ?Application $console = null
    ): ?Application {
        if ($console !== null) {
            $commands = [
                new DownCommand(),
                new UpCommand(),
            ];

            if ($container->has(RepositoryContract::class)) {
                $commands = \array_merge(
                    $commands,
                    [
                        new ConfigCacheCommand(),
                        new ConfigClearCommand(),
                    ]
                );
            }

            $console->addCommands($commands);

            if ($container->has(KernelContract::class) && $container->get(KernelContract::class)->isLocal()) {
                $console->add(new KeyGenerateCommand());

                if (\class_exists(Process::class)) {
                    $console->add(new ServeCommand());
                }
            }
        }

        return $console;
    }
}
