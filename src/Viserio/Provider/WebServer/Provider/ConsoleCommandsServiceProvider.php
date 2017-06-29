<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;
use Viserio\Provider\WebServer\Command\ServerLogCommand;
use Viserio\Provider\WebServer\Command\ServerRunCommand;
use Viserio\Provider\WebServer\Command\ServerStartCommand;
use Viserio\Provider\WebServer\Command\ServerStatusCommand;
use Viserio\Provider\WebServer\Command\ServerStopCommand;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'extendConsoleWithCommands'],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function extendConsoleWithCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        $console = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($console !== null) {
            $console->addCommands([
                new ServerLogCommand(),
                new ServerRunCommand(),
                new ServerStartCommand(),
                new ServerStatusCommand(),
                new ServerStopCommand(),
            ]);
        }

        return $console;
    }
}
