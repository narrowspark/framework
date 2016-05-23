<?php
namespace Viserio\Cookie\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Cookie\Cookie;
use Viserio\Cookie\RequestCookie;

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

        $this->app->singleton('request-cookie', function () {
            return new RequestCookie();
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
            'cookie',
            'request-cookie',
        ];
    }
}
