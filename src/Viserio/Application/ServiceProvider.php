<?php
namespace Viserio\Application;

use Viserio\Container\ServiceProvider as ContainerServiceProvider;
use Viserio\Contracts\Application\ServiceProvider as ServiceProviderContract;

abstract class ServiceProvider extends ContainerServiceProvider implements ServiceProviderContract
{
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
        $config = $this->container->get('config')->get($key, []);
        $this->container->get('config')->set($key, array_merge(require $path, $config));
    }
}
