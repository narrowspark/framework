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

        $this->app->singleton('stack.request', function () {
            return new RequestStack();
        });
    }

    public function aliases()
    {
        return ['stack.request' => 'Symfony\Component\HttpFoundation\RequestStack'];
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
            'stack.request',
        ];
    }
}
