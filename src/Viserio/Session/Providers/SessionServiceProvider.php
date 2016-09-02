<?php
declare(strict_types=1);
namespace Viserio\Session\Providers;

use Interop\Container\ServiceProvider;

class SessionServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        $this->app->singleton('session', function () {
        });

        $this->registerCsrf();
        $this->registerFlash();
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
    public function provides(): array
    {
        return [
            'session',
            'flash',
            'csrf',
        ];
    }

    protected function registerFlash()
    {
        $this->app->singleton('flash', function () {
        });
    }
}
