<?php
namespace Viserio\Session\Providers;

use Viserio\Application\ServiceProvider;

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
