<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL\Provider;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\DriverManager;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class DoctrineDBALServiceProvider implements
    ServiceProviderInterface,
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            Connection::class         => [self::class, 'createConnection'],
            DoctrineConnection::class => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
            'db'                      => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
            'database'                => function (ContainerInterface $container) {
                return $container->get(Connection::class);
            },
            Configuration::class      => [self::class, 'createConfiguration'],
            EventManager::class       => [self::class, 'createEventManager'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', 'dbal'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['default'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'connections' => [
                'mysql' => [
                    'driver'        => 'pdo_mysql',
                    'host'          => 'DB_HOST',
                    'port'          => 'DB_PORT',
                    'database'      => 'DB_DATABASE_NAME',
                    'username'      => 'DB_DATABASE_USER',
                    'password'      => 'DB_DATABASE_PASSWORD',
                    'charset'       => 'UTF8',
                    'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
                'oci8' => [
                    'driver'        => 'oci8',
                ],
            ],
            'wrapperClass' => Connection::class,
        ];
    }

    /**
     * Create a new doctrine connection.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Doctrine\DBAL\Connection
     */
    public static function createConnection(ContainerInterface $container): DoctrineConnection
    {
        return DriverManager::getConnection(
            self::parseConfig(self::resolveOptions($container)),
            $container->get(Configuration::class),
            $container->get(EventManager::class)
        );
    }

    /**
     * Create a new doctrine configuration.
     *
     * @return \Doctrine\DBAL\Configuration
     */
    public static function createConfiguration(): Configuration
    {
        return new Configuration();
    }

    /**
     * Create a new doctrine event manager.
     *
     * @return \Doctrine\Common\EventManager
     */
    public static function createEventManager(): EventManager
    {
        return new EventManager();
    }

    /**
     * Map our config style to doctrine config.
     *
     * @param array $config
     *
     * @return array
     */
    private static function parseConfig(array $config): array
    {
        $connections = $config['connections'][$config['default']];
        $config      = array_merge($config, $connections);

        if (mb_strpos($config['default'], 'sqlite') === false) {
            $config['user']   = $connections['username'];
            $config['dbname'] = $connections['database'];
        } else {
            if (isset($connections['username'])) {
                $config['user'] = $connections['username'];
            }

            $config['path'] = $connections['database'];
        }

        unset($config['default'], $config['connections'], $config['username'], $config['database']);

        return $config;
    }
}
