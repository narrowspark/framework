<?php
namespace Viserio\Session\Providers;

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
 * @version     0.10.0
 */

use Viserio\Application\ServiceProvider;

/**
 * SessionServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class SessionServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('session', function () {

        });

        $this->registerCsrf();
        $this->registerFlash();
    }

    protected function registerFlash()
    {
        $this->app->singleton('flash', function () {

        });
    }

    public function registerCsrf()
    {
        $this->app->singleton('csrf', function () {

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
            'session',
            'flash',
            'csrf',
        ];
    }
}
