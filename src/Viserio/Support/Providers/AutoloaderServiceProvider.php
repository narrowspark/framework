<?php
namespace Viserio\Support\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Support\Autoloader;

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
    public function provides(): array
    {
        return [
            'autoloader',
        ];
    }
}
