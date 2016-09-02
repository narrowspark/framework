<?php
declare(strict_types=1);
namespace Viserio\Http\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Http\Response;

class ResponseServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
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
