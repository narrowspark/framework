<?php
namespace Viserio\Container;

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

use Viserio\Contracts\Container\ServiceProvider as ServiceProviderContract;

/**
 * ServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
abstract class ServiceProvider implements ServiceProviderContract
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $provides = [];

    /**
     * {@inheritdoc}
     */
    abstract public function register();

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [];
    }

    /**
     * Dynamically handle missing method calls.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($method === 'boot') {
            return;
        }

        throw new \BadMethodCallException('Call to undefined method ['.sprintf('%s', $method).']');
    }
}
