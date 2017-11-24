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

class DoctrineDBALServiceProvider implements ServiceProviderInterface
{

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
}
