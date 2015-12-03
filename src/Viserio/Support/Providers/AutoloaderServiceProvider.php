<?php
namespace Viserio\Support\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Support\Autoloader;

/**
 * AutoloaderServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class AutoloaderServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('autoloader', function () {
            return new Autoloader();
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
            'autoloader',
        ];
    }
}
