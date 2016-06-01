<?php
namespace Viserio\Http\Providers;

use Symfony\Component\HttpFoundation\RequestStack;
use Viserio\Application\ServiceProvider;
use Viserio\Http\Request;

class RequestServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
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
