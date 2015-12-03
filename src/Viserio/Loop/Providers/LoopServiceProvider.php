<?php
namespace Viserio\Events\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Loop\Loop;

class LoopServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('loop', function ($app) {
            $loop = new Loop();
            $loop->setContainer($app);

            return $loop;
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
            'loop',
        ];
    }
}
