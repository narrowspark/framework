<?php
declare(strict_types=1);
namespace Viserio\Support\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Support\Autoloader;

class AutoloaderServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
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
