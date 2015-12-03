<?php
namespace Viserio\Events\Providers;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Viserio\Application\ServiceProvider;
use Viserio\Events\Dispatcher;

/**
 * EventsServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class EventsServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('events', function ($app) {
            return new Dispatcher(new EventDispatcher(), $app);
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
            'events',
        ];
    }
}
