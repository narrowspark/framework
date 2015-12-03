<?php
namespace Viserio\Session\Providers;

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
