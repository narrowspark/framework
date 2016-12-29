<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use PDO;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Swift_Mailer;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\WebProfiler\AssetsRenderer;
use Viserio\WebProfiler\DataCollectors\AjaxRequestsDataCollector;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\Psr6CacheDataCollector;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;
use Viserio\WebProfiler\DataCollectors\Bridge\SwiftMailDataCollector;
use Viserio\WebProfiler\DataCollectors\MemoryDataCollector;
use Viserio\WebProfiler\DataCollectors\PhpInfoDataCollector;
use Viserio\WebProfiler\DataCollectors\TimeDataCollector;
use Viserio\WebProfiler\WebProfiler;
use Viserio\WebProfiler\Bridge\DataCollectors\PDO\PDODataCollector;
use Viserio\WebProfiler\Bridge\DataCollectors\PDO\TraceablePDODecorater;
use Viserio\WebProfiler\Bridge\DataCollectors\PDO\TraceablePDOStatementDecorater;

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
            CacheItemPoolInterface::class         => [self::class, 'createCacheItemPoolDecorater'],
            TraceablePDODecorater::class          => [self::class, 'createTraceablePDODecorater'],
            PDO::class                            => function (ContainerInterface $container) {
                return $container->get(TraceablePDODecorater::class);
            },
            RouterContract::class                 => [self::class, 'registerWebProfilerAssetsControllers'],
            TraceablePDOStatementDecorater::class => [self::class, 'createTraceablePDOStatementDecorater'],
            AssetsRenderer::class                 => [self::class, 'createAssetsRenderer'],
            WebProfiler::class                    => [self::class, 'createWebProfiler'],
            WebProfilerContract::class            => function (ContainerInterface $container) {
                return $container->get(WebProfiler::class);
            },
        ];
    }

    public static function createCacheItemPoolDecorater(ContainerInterface $container): CacheItemPoolInterface
    {
        return new TraceableCacheItemDecorater($container->get(CacheItemPoolInterface::class));
    }

    public static function createTraceablePDODecorater(ContainerInterface $container): TraceablePDODecorater
    {
        return new TraceablePDODecorater($container->get(PDO::class));
    }

    public static function createTraceablePDOStatementDecorater(ContainerInterface $container): TraceablePDOStatementDecorater
    {
        return new TraceablePDOStatementDecorater($container->get(TraceablePDODecorater::class));
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
                'namespace' => 'Viserio\WebProfiler\Controllers',
                'prefix'    => 'webprofiler',
            ],
            function ($router) {
                $router->get('assets/stylesheets', [
                    'uses' => 'AssetController::css',
                    'as'   => 'webprofiler.assets.css',
                ]);
                $router->get('assets/javascript', [
                    'uses' => 'AssetController::js',
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

        self::registerSwiftmail($container, $profiler);

        if (self::getConfig($container, 'collector.ajax', false)) {
            $profiler->addCollector(new AjaxRequestsDataCollector());
        }

        if (self::getConfig($container, 'collector.phpinfo', false)) {
            $profiler->addCollector(new PhpInfoDataCollector());
        }

        self::registerCache($container, $profiler);
    }

    private static function registerPDO(ContainerInterface $container, WebProfiler $profiler)
    {
        if (self::getConfig($container, 'collector.pdo', false)) {
            $profiler->addCollector(new PDODataCollector(
                $container->get(TraceablePDODecorater::class)
            ));
        }
    }

    private static function registerSwiftmail(ContainerInterface $container, WebProfiler $profiler)
    {
        if (self::getConfig($container, 'collector.swiftmail', false)) {
            $profiler->addCollector(new SwiftMailDataCollector(
                $container->get(Swift_Mailer::class)
            ));
        }
    }

    private static function registerCache(ContainerInterface $container, WebProfiler $profiler)
    {
        if (self::getConfig($container, 'collector.cache', false)) {
            $cache = new Psr6CacheDataCollector();

            if ($container->has(CacheItemPoolInterface::class)) {
                $cache->addPool($container->get(CacheItemPoolInterface::class));
            }

            $profiler->addCollector($cache);
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
