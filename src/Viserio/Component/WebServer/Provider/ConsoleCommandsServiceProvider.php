<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Provider;

use Interop\Container\ServiceProviderInterface;
use Monolog\Formatter\FormatterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\VarDumper\Server\DumpServer;
use Symfony\Component\VarDumper\VarDumper;
use Viserio\Bridge\Monolog\Formatter\ConsoleFormatter;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\WebServer\Command\ServerDumpCommand;
use Viserio\Component\WebServer\Command\ServerLogCommand;
use Viserio\Component\WebServer\Command\ServerServeCommand;
use Viserio\Component\WebServer\Command\ServerStartCommand;
use Viserio\Component\WebServer\Command\ServerStatusCommand;
use Viserio\Component\WebServer\Command\ServerStopCommand;

class ConsoleCommandsServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

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
        return ['viserio'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        $commands = [
            'server:serve'    => ServerServeCommand::class,
            'server:start'    => ServerStartCommand::class,
            'server:stop'     => ServerStopCommand::class,
            'server:status'   => ServerStatusCommand::class,
        ];

        if (\class_exists(VarDumper::class)) {
            $commands['server:dump'] = ServerDumpCommand::class;
        }

        if (\class_exists(ConsoleFormatter::class) && \interface_exists(FormatterInterface::class)) {
            $commands['server:log'] = ServerLogCommand::class;
        }

        return [
            'console' => [
                'lazily_commands' => $commands,
            ],
            'webserver' => [
                'web_folder' => 'public',
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
            $resolvedOptions = self::resolveOptions($container->get('config'));

            if (\class_exists(ConsoleFormatter::class) && \interface_exists(FormatterInterface::class)) {
                $console->add(new ServerLogCommand());
            }

            $documentRoot = null;
            $env          = null;

            if ($container->has(ConsoleKernelContract::class)) {
                $kernel = $container->get(ConsoleKernelContract::class);

                $documentRoot = $kernel->getRootDir() . '/' . \ltrim($resolvedOptions['webserver']['web_folder'], '/');
                $env          = $kernel->getEnvironment();
            }

            if (\class_exists(VarDumper::class)) {
                $console->add(new ServerDumpCommand($container->get(DumpServer::class)));
            }

            $console->addCommands([
                new ServerServeCommand($documentRoot, $env),
                new ServerStartCommand($documentRoot, $env),
                new ServerStatusCommand(),
                new ServerStopCommand(),
            ]);
        }

        return $console;
    }
}
