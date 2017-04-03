<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\Cache\Psr6CacheDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;

class WebProfilerPsr6CacheBridgeServiceProvider implements ServiceProvider
{
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

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\WebProfiler\WebProfiler
     */
    public static function createWebProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?WebProfilerContract
    {
        if ($getPrevious !== null) {
            $profiler = $getPrevious();
            $cache    = new Psr6CacheDataCollector();

            if ($container->has(CacheItemPoolInterface::class)) {
                $cache->addPool($container->get(CacheItemPoolInterface::class));
            }

            $profiler->addCollector($cache);

            return $profiler;
        }

        return null;
    }
}
