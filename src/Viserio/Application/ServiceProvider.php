<?php
namespace Viserio\Application;

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

use Viserio\Contracts\Application\ServiceProvider as ServiceProviderContract;

/**
 * ServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
abstract class ServiceProvider implements ServiceProviderContract
{
    /**
     * @var array
     */
    protected $provides = [];

    /**
     * The application instance.
     *
     * @var \Viserio\Contracts\Application\Foundation
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param \Viserio\Contracts\Application\Foundation $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

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
     * Subscribe events.
     *
     * @param array|null $commands
     */
    public function commands(array $commands = null)
    {
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param string $path
     * @param string $key
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app->get('config')->get($key, []);
        $this->app->get('config')->set($key, array_merge(require $path, $config));
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
