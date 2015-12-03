<?php
namespace Viserio\Support\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Support\Debug\Dumper;

/**
 * DebugServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class DebugServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind('dumper', function () {
            return new Dumper();
        });
    }
}
