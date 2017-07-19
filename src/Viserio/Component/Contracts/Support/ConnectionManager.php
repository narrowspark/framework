<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support;

use Closure;

interface ConnectionManager
{
    /**
     * Get manager config.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Get a connection instance.
     *
     * @param null|string $name
     *
     * @return mixed
     */
    public function getConnection(?string $name = null);

    /**
     * Reconnect to the given connection.
     *
     * @param null|string $name
     *
     * @return object
     */
    public function reconnect(string $name = null);

    /**
     * Disconnect from the given connection.
     *
     * @param null|string $name
     *
     * @return void
     */
    public function disconnect(string $name = null): void;

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string;

    /**
     * Set the default connection name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefaultConnection(string $name): void;

    /**
     * Register a custom connection creator.
     *
     * @param string   $driver
     * @param \Closure $callback
     *
     * @return void
     */
    public function extend(string $driver, Closure $callback): void;

    /**
     * Return all of the created connections.
     *
     * @return object[]
     */
    public function getConnections(): array;

    /**
     * Check if the given connect is supported.
     *
     * @param string $connect
     *
     * @return bool
     */
    public function hasConnection(string $connect): bool;

    /**
     * Get the configuration for a connection.
     *
     * @param string $name
     *
     * @return array
     */
    public function getConnectionConfig(string $name): array;

    /**
     * Make the connection instance.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function createConnection(array $config);
}
