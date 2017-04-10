<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\Psr6CacheDataCollector;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;

class ProfilerPsr6CacheBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheItemPoolInterface::class => [self::class, 'createCacheItemPoolDecorater'],
            ProfilerContract::class    => [self::class, 'createProfiler'],
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
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function createProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
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
