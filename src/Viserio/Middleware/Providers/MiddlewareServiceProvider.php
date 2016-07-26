<?php
declare(strict_types=1);
namespace Viserio\Middleware\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Middleware\Dispatcher;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
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
