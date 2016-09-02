<?php
declare(strict_types=1);
namespace Viserio\Http\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Http\Request;

class RequestServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        $this->app->singleton('request', function () {
            return new Request();
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
            'request',
        ];
    }
}
