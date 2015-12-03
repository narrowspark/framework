<?php
namespace Viserio\Contracts\Application;

use Viserio\Contracts\Container\Container;

/**
 * Foundation.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Foundation extends Container
{
    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return string
     */
    public function environment();

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance();

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
    public function detectEnvironment(\Closure $callback);

    /**
     * Register a service provider with the application.
     *
     * @param string $provider
     * @param array  $options
     * @param bool   $force
     *
     * @return \Viserio\Contract\Application\ServiceProvider
     */
    public function register($provider, $options = [], $force = false);
}
