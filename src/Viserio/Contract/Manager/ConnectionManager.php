<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Manager;

use Closure;

interface ConnectionManager
{
    /**
     * Get manager config.
     */
    public function getConfig(): array;

    /**
     * Get a connection instance.
     */
    public function getConnection(?string $name = null);

    /**
     * Reconnect to the given connection.
     */
    public function reconnect(?string $name = null): object;

    /**
     * Disconnect from the given connection.
     */
    public function disconnect(?string $name = null): void;

    /**
     * Get the default connection name.
     */
    public function getDefaultConnection(): string;

    /**
     * Set the default connection name.
     */
    public function setDefaultConnection(string $name): void;

    /**
     * Register a custom connection creator.
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
     */
    public function hasConnection(string $connect): bool;

    /**
     * Get the configuration for a connection.
     */
    public function getConnectionConfig(string $name): array;

    /**
     * Make the connection instance.
     *
     * @throws \Viserio\Contract\Manager\Exception\InvalidArgumentException
     */
    public function createConnection(array $config);
}
