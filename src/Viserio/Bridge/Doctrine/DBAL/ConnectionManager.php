<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Viserio\Component\Support\AbstractConnectionManager;

class ConnectionManager extends AbstractConnectionManager
{
    /**
     * A doctrine event manager instance.
     *
     * @var null|\Doctrine\Common\EventManager
     */
    private $doctrineEventManager;

    /**
     * A doctrine configuration instance.
     *
     * @var null|\Doctrine\DBAL\Configuration
     */
    private $doctrineConfiguration;

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', static::getConfigName()];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'default'     => 'mysql',
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
                'sqlite' => [
                    'driver'        => 'pdo_sqlite',
                    'username'      => 'DB_DATABASE_USER',
                    'password'      => 'DB_DATABASE_PASSWORD',
                    'path'          => 'DB_PATH',
                ],
            ],
        ];
    }

    /**
     * Set a doctrine event manager instance.
     *
     * @param \Doctrine\Common\EventManager $doctrineEventManager
     *
     * @return void
     */
    public function setDoctrineEventManager(EventManager $doctrineEventManager): void
    {
        $this->doctrineEventManager = $doctrineEventManager;
    }

    /**
     * Get a doctrine event manager instance or null.
     *
     * @return null|\Doctrine\Common\EventManager
     */
    public function getDoctrineEventManager(): ?EventManager
    {
        return $this->doctrineEventManager;
    }

    /**
     * Set a doctrine event manager instance.
     *
     * @param \Doctrine\DBAL\Configuration $doctrineConfiguration
     *
     * @return void
     */
    public function setDoctrineConfiguration(Configuration $doctrineConfiguration): void
    {
        $this->doctrineConfiguration = $doctrineConfiguration;
    }

    /**
     * Get a doctrine event manager instance or null.
     *
     * @return null|\Doctrine\DBAL\Configuration
     */
    public function getDoctrineConfiguration(): ?Configuration
    {
        return $this->doctrineConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionConfig(string $name): array
    {
        return self::parseConfig(parent::getConnectionConfig($name));
    }

    /**
     * Create a new mysql doctrine connection.
     *
     * @param array $config
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return \Viserio\Bridge\Doctrine\DBAL\Connection|\Doctrine\DBAL\Connection
     */
    protected function createMysqlConnection(array $config): Connection
    {
        return DriverManager::getConnection($config, $this->getDoctrineConfiguration(), $this->getDoctrineEventManager());
    }

    /**
     * Create a new sqlite doctrine connection.
     *
     * @param array $config
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return \Viserio\Bridge\Doctrine\DBAL\Connection|\Doctrine\DBAL\Connection
     */
    protected function createSqliteConnection(array $config): Connection
    {
        return DriverManager::getConnection($config, $this->getDoctrineConfiguration(), $this->getDoctrineEventManager());
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'dbal';
    }

    /**
     * Map our config style to doctrine config.
     *
     * @param array $options
     *
     * @return array
     */
    private static function parseConfig(array $options): array
    {
        $doctrineConfig = [];
        $config         = $options['connections'][$options['default']];

        $doctrineConfig['wrapperClass'] = $config['wrapper_class'] ?? Connection::class;

        if (\mb_strpos($config['default'], 'sqlite') === false) {
            $doctrineConfig['user']   = $config['username'];
            $doctrineConfig['dbname'] = $config['database'];
        } else {
            if (isset($config['username'])) {
                $doctrineConfig['user'] = $config['username'];
            }

            $doctrineConfig['path'] = $config['database'];
        }

        unset($config['default'], $config['connections'], $config['username'], $config['database']);

        return array_merge($config, $doctrineConfig);
    }
}
