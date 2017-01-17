<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\WebProfiler\AssetsRenderer;
use Viserio\Component\WebProfiler\DataCollectors\AjaxRequestsDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\MemoryDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\PhpInfoDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\TimeDataCollector;
use Viserio\Component\WebProfiler\WebProfiler;

class WebProfilerServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            RouterContract::class      => [self::class, 'registerWebProfilerAssetsControllers'],
            AssetsRenderer::class      => [self::class, 'createAssetsRenderer'],
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
            WebProfiler::class         => function (ContainerInterface $container) {
                return $container->get(WebProfilerContract::class);
            },
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = new WebProfiler($container->get(AssetsRenderer::class));

        if (self::getConfig($container, 'enable')) {
            $profiler->enable();
        }

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

    public static function registerWebProfilerAssetsControllers(ContainerInterface $container): RouterContract
    {
        $router = $container->get(RouterContract::class);

        $router->group(
            [
                'namespace' => 'Viserio\Component\WebProfiler\Controllers',
                'prefix'    => 'webprofiler',
            ],
            function ($router) {
                $router->get('assets/stylesheets', [
                    'uses' => 'AssetController@css',
                    'as'   => 'webprofiler.assets.css',
                ]);
                $router->get('assets/javascript', [
                    'uses' => 'AssetController@js',
                    'as'   => 'webprofiler.assets.js',
                ]);
            }
        );

        return $router;
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

        if (self::getConfig($container, 'collector.ajax', false)) {
            $profiler->addCollector(new AjaxRequestsDataCollector());
        }

        if (self::getConfig($container, 'collector.phpinfo', false)) {
            $profiler->addCollector(new PhpInfoDataCollector());
        }
    }

    private static function registerCollectorsFromConfig(ContainerInterface $container, WebProfiler $profiler)
    {
        if (($collectors = self::getConfig($container, 'collectors', null)) !== null) {
            foreach ($collectors as $collector) {
                $profiler->addCollector($container->get($collector));
            }
        }
    }
}
