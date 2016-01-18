<?php
namespace Viserio\Application;

use Viserio\Contracts\Application\ServiceProvider as ServiceProviderContract;

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
     * Alias a type to a shorter name.
     *
     * @param string $abstract
     * @param string $alias
     */
    protected function alias($abstract, $alias)
    {
        if ($alias) {
            $this->app->alias($abstract, $alias);
        }
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

        throw new \BadMethodCallException('Call to undefined method [' . sprintf('%s', $method) . ']');
    }
}
