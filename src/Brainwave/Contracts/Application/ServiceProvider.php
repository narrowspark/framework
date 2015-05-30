<?php

namespace Brainwave\Contracts\Application;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

/**
 * ServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
interface ServiceProvider
{
    /**
     * Use the register method to register items with the container via the
     * protected $this->app property.
     */
    public function register();

    /**
     * Subscribe events.
     *
     * @param array|null $commands
     */
    public function commands(array $commands = null);

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides();

    /**
     * Dynamically handle missing method calls.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters);
}
