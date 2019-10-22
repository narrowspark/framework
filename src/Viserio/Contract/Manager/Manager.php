<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Manager;

use Closure;

interface Manager
{
    /**
     * Get manager config.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string;

    /**
     * Set the default driver name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefaultDriver(string $name): void;

    /**
     * Get a driver instance.
     *
     * @param null|string $driver
     *
     * @return mixed
     */
    public function getDriver(?string $driver = null);

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param \Closure $callback
     *
     * @return void
     */
    public function extend(string $driver, Closure $callback): void;

    /**
     * Get all of the created "drivers".
     *
     * @return array
     */
    public function getDrivers(): array;

    /**
     * Check if the given driver is supported.
     *
     * @param string $driver
     *
     * @return bool
     */
    public function hasDriver(string $driver): bool;

    /**
     * Get the configuration for a driver.
     *
     * @param string $name
     *
     * @return array
     */
    public function getDriverConfig(string $name): array;

    /**
     * Make a new driver instance.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Manager\Exception\InvalidArgumentException
     *
     * @return mixed
     */
    public function createDriver(array $config);
}
