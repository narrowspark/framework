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
}
