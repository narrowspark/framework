<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Providers;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Bridge\Doctrine\Connection;

class DatabaseServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.database';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Connection::class    => [self::class, 'createConnection'],
            Configuration::class => [self::class, 'createConfiguration'],
            EventManager::class  => [self::class, 'createEventManager'],
            'db'                 => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
            'database' => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
            'database.command.helper' => [self::class, 'createDatabaseCommandsHelpser'],
            'database.commands'       => [self::class, 'createDatabaseCommands'],
        ];
    }

    public static function createConnection(ContainerInterface $container): Connection
    {
        return DriverManager::getConnection(
            self::parseConfig(self::getConfig($container, 'database', [])),
            $container->get(Configuration::class),
            $container->get(EventManager::class)
        );
    }

    public static function createConfiguration(): Configuration
    {
        return new Configuration();
    }

    public static function createEventManager(): EventManager
    {
        return new EventManager();
    }

    public static function createDatabaseCommands(): array
    {
        return [
            new RunSqlCommand(),
            new ImportCommand(),
            new ReservedWordsCommand(),
        ];
    }

    public static function createDatabaseCommandsHelpser(ContainerInterface $container): HelperSet
    {
        return new HelperSet([
            'db' => new ConnectionHelper($container->get(Connection::class)),
        ]);
    }

    private static function parseConfig(array $config): array
    {
        $connections = $config['connections'][$config['default']];
        $config      = array_merge($config, $connections);

        if (mb_strpos($config['default'], 'sqlite') === false) {
            $config['user']   = $connections['username'];
            $config['dbname'] = $connections['database'];

            if (empty($config['dbname'])) {
                throw new DBALException('The "database" must be set in the config or container entry "database"');
            }
        } else {
            if (isset($connections['username'])) {
                $config['user'] = $connections['username'];
            }

            $config['path'] = $connections['database'];
        }

        unset($config['default'], $config['connections'], $config['username'], $config['database']);

        $config['wrapperClass'] = $config['wrapperClass'] ?? Connection::class;

        return $config;
    }
}
