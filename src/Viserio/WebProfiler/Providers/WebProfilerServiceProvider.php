<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\WebProfiler\AssetsRenderer;
use Viserio\WebProfiler\DataCollectors\MemoryDataCollector;
use Viserio\WebProfiler\DataCollectors\TimeDataCollector;
use Viserio\WebProfiler\WebProfiler;

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
            AssetsRenderer::class => [self::class, 'createAssetsRenderer'],
            WebProfiler::class => [self::class, 'createWebProfiler'],
            WebProfilerContract::class => function (ContainerInterface $container) {
                return $container->get(WebProfiler::class);
            },
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = new WebProfiler($container->get(AssetsRenderer::class));

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

        self::registerCollectorsFromConfig($container, $profiler);
        self::registerCollectors($container, $profiler);

        return $profiler;
    }

    public static function createAssetsRenderer(ContainerInterface $container): AssetsRenderer
    {
        return new AssetsRenderer(
            self::getConfig($container, 'jquery_is_used', false),
            self::getConfig($container, 'path')
        );
    }

    protected static function registerCollectors(ContainerInterface $container, WebProfiler $profiler)
    {
        if (self::getConfig($container, 'collector.time', true)) {
            $profiler->addCollector(new TimeDataCollector(
                $container->get(ServerRequestInterface::class)
            ));
        }

        if (self::getConfig($container, 'collector.memory', true)) {
            $profiler->addCollector(new MemoryDataCollector());
        }
    }

    protected static function registerCollectorsFromConfig(ContainerInterface $container, WebProfiler $profiler)
    {
        if (($collectors = self::getConfig($container, 'collectors', null)) !== null) {
            foreach ($collectors as $collector) {
                $profiler->addCollector($container->get($collector));
            }
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
