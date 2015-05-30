<?php

namespace Brainwave\Cache\Providers;

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

use Brainwave\Application\ServiceProvider;
use Brainwave\Cache\Manager as CacheManager;

/**
 * CacheServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerCacheFactory();
        $this->registerDefaultCache();
        $this->registerCaches();
    }

    protected function registerCacheFactory()
    {
        $this->app->singleton('cache.factory', function ($app) {
            $cacheFactory = new CacheManager(
                $app->get('config'),
                $app->get('files'),
                $app->get('config')->get('cache::supported.drivers', [])
            );

            $cacheFactory->setPrefix($app->get('config')->get('cache::prefix'));

            return $cacheFactory;
        });
    }

    protected function registerDefaultCache()
    {
        $this->app->singleton('cache.store', function ($app) {
            //The default driver
            $app->get('cache.factory')->setDefaultDriver($app->get('config')->get('cache::driver'));

            return $app->get('cache.factory')->driver($app->get('cache.factory')->getDefaultDriver());
        });
    }

    protected function registerCaches()
    {
        if (($cache = $this->app->get('config')->get('cache::caches')) !== null) {
            foreach ($cache as $name => $class) {
                if ($this->app->get('cache.factory')->getDefaultDriver() === $name) {
                    // we use shortcuts here in case the default has been overridden
                    $config = $this->app->get('config')->get('cache::driver');
                } else {
                    $config = $cache[$name];
                }

                $this->app->singleton(sprintf('cache.%s.store', $name), function ($app) use ($config) {
                    return $app->get('cache.factory')->driver($config['driver'], $config);
                });
            }
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'cache',
            'cache.factory',
        ];
    }
}
