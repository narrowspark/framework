<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL\Providers;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class DoctrineDBALServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

    /**
     * {@inheritdoc}
     */
    public function getServices()
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
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine'];
    }

    /**
     * Create a new doctrine connection.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return \Doctrine\DBAL\Connection
     */
    public static function createConnection(ContainerInterface $container): DoctrineConnection
    {
        self::resolveOptions($container);

        return DriverManager::getConnection(
            self::parseConfig(self::$options),
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
     * @return array
     */
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

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
