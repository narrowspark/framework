<?php
namespace Viserio\Cookie\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Cookie\Cookie;

class CookieServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('cookie', function () {
            return new Cookie();
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
            'cookie',
        ];
    }
}
