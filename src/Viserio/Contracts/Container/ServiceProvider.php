<?php
namespace Viserio\Contracts\Container;

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
 * @since   0.10.0-dev
 */
interface ServiceProvider
{
    /**
     * Use the register method to register items with the container.
     */
    public function register();

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides();

    /**
     * Method will be invoked on registration of a service provider.
     * Provides ability for eager loading of Service Providers.
     *
     * @return void
     */
    public function boot();

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
