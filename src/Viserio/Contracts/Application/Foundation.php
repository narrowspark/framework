<?php
namespace Viserio\Contracts\Application;

use Viserio\Contracts\Container\Container;

interface Foundation extends Container
{
    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return string
     */
    public function environment(): string;

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance(): bool;

    /**
     * Boot the application's service providers.
     */
    public function boot();

    /**
     * Register a new boot listener.
     *
     * @param mixed $callback
     */
    public function booting($callback);

    /**
     * Register a new "booted" listener.
     *
     * @param mixed $callback
     */
    public function booted($callback);

    /**
     * Detect the application's current environment.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    public function detectEnvironment(\Closure $callback): string;

    /**
     * Register a service provider with the application.
     *
     * @param string $provider
     * @param array  $options
     * @param bool   $force
     *
     * @return \Viserio\Contract\Application\ServiceProvider
     */
    public function register(string $provider, array $options = [], bool $force = false): \Viserio\Contract\Application\ServiceProvider;
}
