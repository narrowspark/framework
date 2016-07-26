<?php
declare(strict_types=1);
namespace Viserio\Pipeline\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Pipeline\Pipeline;

class PipelineServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('pipeline', function ($app) {
            return new Pipeline($app);
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
            Pipeline::class,
        ];
    }
}
