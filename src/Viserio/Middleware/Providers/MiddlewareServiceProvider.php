<?php
declare(strict_types=1);
namespace Viserio\Middleware\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Middleware\Dispatcher;

class MiddlewareServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        $this->app->singleton('middleware', function ($app) {
            return (new Dispatcher())->setContainer($app);
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
            Dispatcher::class,
        ];
    }
}
