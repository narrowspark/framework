<?php

namespace Brainwave\Filesystem\Providers;

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
use Brainwave\Filesystem\Adapters\ConnectionFactory as Factory;
use Brainwave\Filesystem\FileLoader;
use Brainwave\Filesystem\Filesystem;
use Brainwave\Filesystem\FilesystemManager;

/**
 * FilesystemServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('files', function () {
            return new Filesystem();
        });

        $this->registerFlysystem();
        $this->registerFileLoader();
    }

    /**
     * Register the driver based filesystem.
     */
    protected function registerFlysystem()
    {
        $this->registerFactory();

        $this->registerManager();

        $this->app->bind('filesystem.disk', function ($app) {
            return $app->get('filesystem')->disk($app->get('config')->get('filesystems::default'));
        });

        $this->app->bind('filesystem.cloud', function ($app) {
            return $app->get('filesystem')->disk($app->get('config')->get('filesystems::cloud'));
        });
    }

    /**
     * Register the filesystem factory.
     */
    protected function registerFactory()
    {
        $this->app->singleton('filesystem.factory', function () {
            return new Factory();
        });
    }

    /**
     * Register the filesystem manager.
     */
    protected function registerManager()
    {
        $this->app->singleton('filesystem', function ($app) {
            return new FilesystemManager($app->get('config'), $app->get('filesystem.factory'));
        });
    }

    protected function registerFileLoader()
    {
        $this->app->singleton('file.loader', function ($app) {
            $app->bind('files.path', '');

            return new FileLoader($app->get('files'), $app->get('files.path'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'flysystem',
            'flysystem.factory',
            'filesystem.disk',
            'filesystem.cloud',
            'file.loader',
        ];
    }
}
