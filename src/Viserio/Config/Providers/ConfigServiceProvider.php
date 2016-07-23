<?php

declare(strict_types=1);
namespace Viserio\Config\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;
use Viserio\Filesystem\FileLoader;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('config.repository', function () {
            return new Repository();
        });

        $this->app->singleton('config', function ($app) {
            return new ConfigManager(
                $app->get('config.repository'),
                new FileLoader($app->get('files'), $app->get('settings.path'))
            );
        });
    }
}
