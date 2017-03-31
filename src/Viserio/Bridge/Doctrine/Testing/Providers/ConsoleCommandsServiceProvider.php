<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
// use Viserio\Bridge\Doctrine\Testing\Commands\LoadDataFixturesDoctrineCommand;
use Viserio\Component\Console\Application;

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
     * Extend viserio console with new command.
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

            // $console->add(new LoadDataFixturesDoctrineCommand());

            return $console;
        }

        return null;
    }
}
