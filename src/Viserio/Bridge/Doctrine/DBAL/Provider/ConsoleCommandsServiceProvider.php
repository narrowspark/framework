<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL\Provider;

use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Component\Console\Application;

class ConsoleCommandsServiceProvider implements ServiceProviderInterface
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
            $console->setHelperSet(new HelperSet([
                'db' => new ConnectionHelper($container->get(Connection::class)),
            ]));

            $console->addCommands([
                new RunSqlCommand(),
                new ImportCommand(),
                new ReservedWordsCommand(),
            ]);
        }

        return $console;
    }
}
