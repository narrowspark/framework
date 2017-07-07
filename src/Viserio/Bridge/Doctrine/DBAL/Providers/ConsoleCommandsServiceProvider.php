<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL\Providers;

use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Viserio\Bridge\Doctrine\DBAL\Connection;
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
     * Extend viserio console with new commands.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        $console = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($console !== null) {
            $console->setHelperSet(new HelperSet([
                'db' => new ConnectionHelper($container->get(Connection::class)),
            ]));

            $console->addCommands([
                new RunSqlCommand(),
                new ImportCommand(),
                new ReservedWordsCommand(),
            ]);

            return $console;
        }

        return $console;
    }
}
