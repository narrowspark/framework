<?php
namespace Viserio\Routing\Providers;

use FastRoute\DataGenerator\GroupCountBased;
use Viserio\Application\ServiceProvider;
use Viserio\Routing\RouteCollection;
use Viserio\Routing\RouteParser;
use Viserio\Routing\UrlGenerator\GroupCountBasedDataGenerator;
use Viserio\Routing\UrlGenerator\SimpleUrlGenerator;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('route', function ($app) {
            return new RouteCollection(
                $app,
                new RouteParser(),
                new GroupCountBased()
            );
        });

        $this->registerUrlGenerator();
    }

    public function boot()
    {
        require $this->app->path() . '/Http/routes.php';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'route',
            'route.url.generator',
            'route.url.data.generator',
        ];
    }

    protected function registerUrlGenerator()
    {
        $this->registerUrlGeneratorDataGenerator();

        $this->app->singleton('route.url.generator', function ($app) {
            return (new SimpleUrlGenerator($app->get('route.url.data.generator')))->setRequest($app['request']);
        });
    }

    protected function registerUrlGeneratorDataGenerator()
    {
        $this->app->singleton('route.url.data.generator', function ($app) {
            return new GroupCountBasedDataGenerator($app->get('route'));
        });
    }
}
