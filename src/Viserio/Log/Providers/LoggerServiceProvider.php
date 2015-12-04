<?php
namespace Viserio\Log\Providers;

use Monolog\Logger;
use Viserio\Application\ServiceProvider;
use Viserio\Log\Writer as MonologWriter;

class LoggerServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('logger', function ($app) {
            return new MonologWriter(
                new Logger($app->get('env')),
                $app->get('events')
            );
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
            'logger',
        ];
    }
}
