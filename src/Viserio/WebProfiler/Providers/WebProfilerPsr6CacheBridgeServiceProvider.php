<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Providers;

use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\Psr6CacheDataCollector;

class WebProfilerPsr6CacheBridgeServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheItemPoolInterface::class => [self::class, 'createCacheItemPoolDecorater'],
            WebProfilerContract::class    => [self::class, 'createWebProfiler'],
        ];
    }

    public static function createCacheItemPoolDecorater(ContainerInterface $container): TraceableCacheItemDecorater
    {
        return new TraceableCacheItemDecorater($container->get(CacheItemPoolInterface::class));
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = $container->get(WebProfilerContract::class);

        if (self::getConfig($container, 'collector.cache', false)) {
            $cache = new Psr6CacheDataCollector();

            if ($container->has(CacheItemPoolInterface::class)) {
                $cache->addPool($container->get(CacheItemPoolInterface::class));
            }

            $profiler->addCollector($cache);
        }

        return $profiler;
    }
}
