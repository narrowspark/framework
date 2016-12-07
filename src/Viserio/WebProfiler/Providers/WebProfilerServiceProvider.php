<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\WebProfiler\DataCollectors\ConfigDataCollector;
use Viserio\WebProfiler\DataCollectors\MemoryDataCollector;
use Viserio\WebProfiler\DataCollectors\RequestDataCollector;
use Viserio\WebProfiler\DataCollectors\NarrowsparkDataCollector;
use Viserio\WebProfiler\WebProfiler;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Foundation\Application;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class WebProfilerServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            WebProfiler::class => [self::class, 'createWebProfiler'],
            WebProfilerContract::class => function (ContainerInterface $container) {
                return $container->get(WebProfiler::class);
            },
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = new WebProfiler();

        if ($container->has(CacheItemPoolInterface::class)) {
            $profiler->setCacheItemPool($container->get(CacheItemPoolInterface::class));
        }

        if ($container->has(PsrLoggerInterface::class)) {
            $profiler->setLogger($container->get(PsrLoggerInterface::class));
        }

        $profiler->setStreamFactory(
            $container->get(StreamFactoryInterface::class)
        );

        if ($container->has(UrlGeneratorContract::class)) {
            // self::registerControllers($container);

            // $profiler->setUrlGenerator(
            //     $container->get(UrlGeneratorContract::class)
            // );
        }

        self::registerCollectors($container, $profiler);

        return $profiler;
    }

    protected static function registerCollectors(ContainerInterface $container, WebProfiler $profiler)
    {
        if (self::getConfig($container, 'collector.request', true)) {
            $profiler->addCollector(new RequestDataCollector());
        }

        if (self::getConfig($container, 'collector.narrowspark', true) && class_exists(Application::class)) {
            $profiler->addCollector(new NarrowsparkDataCollector());
        }

        if (self::getConfig($container, 'collector.memory', true)) {
            $profiler->addCollector(new MemoryDataCollector());
        }

        if (self::getConfig($container, 'collector.config', true) && $container->has(RepositoryContract::class)) {
            $profiler->addCollector(new ConfigDataCollector(
                $container->get(RepositoryContract::class)->getAll()
            ));
        }
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
