<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Config\Manager as ConfigManagerContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\WebProfiler\WebProfiler;

class WebProfilerServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            WebProfiler::class => [self::class, 'createWebProfiler'],
        ];
    }

    public static function createWebProfiler(ContainerInterface $container)
    {
        $profiler = new WebProfiler(
            $container->get(ConfigManagerContract::class),
            $container->get(ServerRequestInterface::class)
        );

        $profiler->setStreamFactory(
            $container->get(StreamFactoryInterface::class)
        );

        if ($container->has(UrlGeneratorContract::class)) {
            // self::registerControllers($container);

            // $profiler->setUrlGenerator(
            //     $container->get(UrlGeneratorContract::class)
            // );
        }

        return $profiler;
    }

    protected static function registerControllers(ContainerInterface $container)
    {
        $router = $container->get(RouterContract::class);

        $router->group(
            [
                'namespace' => 'Viserio\WebProfiler\Controllers',
                'prefix' => 'webprofiler',
            ],
            function ($router) {
                $router->get('assets/stylesheets', [
                    'uses' => 'AssetController::css',
                    'as' => 'webprofiler.assets.css',
                ]);
                $router->get('assets/javascript', [
                    'uses' => 'AssetController::js',
                    'as' => 'webprofiler.assets.js',
                ]);
            }
        );
    }
}
