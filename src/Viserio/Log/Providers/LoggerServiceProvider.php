<?php
declare(strict_types=1);
namespace Viserio\Log\Providers;

use Interop\Container\ServiceProvider;
use Monolog\Logger;
use Viserio\Log\Writer as MonologWriter;

class LoggerServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
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
    public function provides(): array
    {
        return [
            'logger',
        ];
    }
}
