<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\DriverException;
use Doctrine\DBAL\Driver\ExceptionConverterDriver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\VersionAwarePlatformDriver;
use PDOException;

class StaticDriver implements Driver, ExceptionConverterDriver, VersionAwarePlatformDriver
{
    /**
     * A list of connections.
     *
     * @var \Doctrine\DBAL\Driver\Connection[]
     */
    private static $connections = [];

    /**
     * Check if connection need to be keep.
     *
     * @var bool
     */
    private static $keepStaticConnections = false;

    /**
     * A driver instance.
     *
     * @var \Doctrine\DBAL\Driver
     */
    private $underlyingDriver;

    /**
     * A platform instance.
     *
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $platform;

    /**
     * Create a new static driver instance.
     *
     * @param \Doctrine\DBAL\Driver                     $underlyingDriver
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function __construct(Driver $underlyingDriver, AbstractPlatform $platform)
    {
        $this->underlyingDriver = $underlyingDriver;
        $this->platform         = $platform;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        if (self::$keepStaticConnections) {
            $key = sha1(serialize($params) . $username . $password);

            if (! isset(self::$connections[$key])) {
                self::$connections[$key] = $this->underlyingDriver->connect($params, $username, $password, $driverOptions);
                self::$connections[$key]->beginTransaction();
            }

            return new StaticConnection(self::$connections[$key]);
        }

        return $this->underlyingDriver->connect($params, $username, $password, $driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return $this->platform;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        return $this->underlyingDriver->getSchemaManager($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->underlyingDriver->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(Connection $conn)
    {
        return $this->underlyingDriver->getDatabase($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function convertException($message, DriverException $exception)
    {
        if ($this->underlyingDriver instanceof ExceptionConverterDriver) {
            return $this->underlyingDriver->convertException($message, $exception);
        }

        return $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatabasePlatformForVersion($version)
    {
        return $this->platform;
    }

    /**
     * Should the connection be kept?
     *
     * @param bool $keepStaticConnections
     *
     * @return void
     */
    public static function setKeepStaticConnections(bool $keepStaticConnections): void
    {
        self::$keepStaticConnections = $keepStaticConnections;
    }

    /**
     * Is the connection held.
     *
     * @return bool
     */
    public static function isKeepStaticConnections(): bool
    {
        return self::$keepStaticConnections;
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public static function beginTransaction(): void
    {
        foreach (self::$connections as $connection) {
            try {
                $connection->beginTransaction();
            } catch (PDOException $exception) {
                // transaction could be started already
            }
        }
    }

    /**
     * Rolls back a transaction.
     *
     * @return void
     */
    public static function rollBack(): void
    {
        foreach (self::$connections as $connection) {
            $connection->rollBack();
        }
    }

    /**
     * Commits a transaction.
     *
     * @return void
     */
    public static function commit(): void
    {
        foreach (self::$connections as $connection) {
            $connection->commit();
        }
    }
}
