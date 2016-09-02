<?php
declare(strict_types=1);
namespace Viserio\Support\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Support\Debug\Dumper;

class DebugServiceProvider implements ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function getServices()
    {
        $this->app->bind('dumper', function () {
            return new Dumper();
        });
    }
}
