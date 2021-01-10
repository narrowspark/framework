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

interface Manager
{
    /**
     * Get manager config.
     */
    public function getConfig(): array;

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string;

    /**
     * Set the default driver name.
     */
    public function setDefaultDriver(string $name): void;

    /**
     * Get a driver instance.
     */
    public function getDriver(?string $driver = null);

    /**
     * Register a custom driver creator Closure.
     */
    public function extend(string $driver, Closure $callback): void;

    /**
     * Get all of the created "drivers".
     */
    public function getDrivers(): array;

    /**
     * Check if the given driver is supported.
     */
    public function hasDriver(string $driver): bool;

    /**
     * Get the configuration for a driver.
     */
    public function getDriverConfig(string $name): array;

    /**
     * Make a new driver instance.
     *
     * @throws \Viserio\Contract\Manager\Exception\InvalidArgumentException
     */
    public function createDriver(array $config);
}
