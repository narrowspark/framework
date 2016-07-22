<?php
declare(strict_types=1);
namespace Viserio\Http\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Http\Response;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('response', function () {
            return new Response();
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
            'response',
        ];
    }
}
