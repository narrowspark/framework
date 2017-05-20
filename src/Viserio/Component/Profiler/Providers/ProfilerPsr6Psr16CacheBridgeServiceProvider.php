<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\Psr6Psr16CacheDataCollector;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\SimpleTraceableCacheDecorator;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorator;

class ProfilerPsr6Psr16CacheBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheItemPoolInterface::class => [self::class, 'createCacheItemPoolDecorator'],
            CacheInterface::class         => [self::class, 'createCacheInterfaceDecorator'],
            ProfilerContract::class       => [self::class, 'createProfiler'],
        ];
    }

    /**
     * Decorate CacheItemPool instances.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Psr\Cache\CacheItemPoolInterface
     */
    public static function createCacheItemPoolDecorator(ContainerInterface $container, ?callable $getPrevious = null): ?CacheItemPoolInterface
    {
        $cache = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($cache !== null) {
            return new TraceableCacheItemDecorator($cache);
        }

        return $cache;
    }

    /**
     * Decorate SimpleTraceableCache instances.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Psr\SimpleCache\CacheInterface
     */
    public static function createCacheInterfaceDecorator(ContainerInterface $container, ?callable $getPrevious = null): ?CacheInterface
    {
        $cache = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($cache !== null) {
            return new SimpleTraceableCacheDecorator($cache);
        }

        return $cache;
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
        $profiler = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($profiler !== null) {
            $collector    = new Psr6Psr16CacheDataCollector();

            if ($container->has(CacheItemPoolInterface::class) || $container->has(CacheInterface::class)) {
                if (($cache = $container->get(CacheItemPoolInterface::class)) instanceof TraceableCacheItemDecorator ||
                    ($cache = $container->get(CacheInterface::class)) instanceof SimpleTraceableCacheDecorator
                ) {
                   $collector->addPool($cache);
                }
            }

            $profiler->addCollector($collector);

            return $profiler;
        }

        return $profiler;
    }
}
