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

use Viserio\Container\ServiceProvider as ContainerServiceProvider;
use Viserio\Contracts\Application\ServiceProvider as ServiceProviderContract;

/**
 * ServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
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
