<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL\Providers;

use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ApplicationContract::class => [self::class, 'createConsoleCommands'],
        ];
    }

    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?ApplicationContract
    {
        if ($getPrevious !== null) {
            $console = $getPrevious();

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

        return null;
    }
}
