<?php

namespace Brainwave\Log\Providers;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Application\ServiceProvider;
use Brainwave\Log\Writer as MonologWriter;
use Monolog\Logger;

/**
 * LoggerServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
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
