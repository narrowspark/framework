<?php
declare(strict_types=1);
namespace Viserio\Support\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Support\Debug\Dumper;

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
