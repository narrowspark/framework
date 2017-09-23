<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Cache\Adapter\Common\PhpCachePool as PhpCachePoolInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\PhpCacheTraceableCacheDecorator;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Psr6Psr16CacheDataCollector;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\SimpleTraceableCacheDecorator;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator;

class ProfilerPsr6Psr16CacheBridgeServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            CacheItemPoolInterface::class => [self::class, 'extendCacheItemPool'],
            CacheInterface::class         => [self::class, 'extendSimpleTraceableCache'],
            ProfilerContract::class       => [self::class, 'extendProfiler'],
        ];
    }

    /**
     * Decorate CacheItemPool instances.
     *
     * @param \Psr\Container\ContainerInterface      $container
     * @param null|\Psr\Cache\CacheItemPoolInterface $cache
     *
     * @return null|\Psr\Cache\CacheItemPoolInterface
     */
    public static function extendCacheItemPool(
        ContainerInterface $container,
        ?CacheItemPoolInterface $cache = null
    ): ?CacheItemPoolInterface {
        if ($cache !== null) {
            if ($cache instanceof PhpCachePoolInterface) {
                return new PhpCacheTraceableCacheDecorator($cache);
            }

            return new TraceableCacheItemDecorator($cache);
        }

        return $cache;
    }

    /**
     * Decorate SimpleTraceableCache instances.
     *
     * @param \Psr\Container\ContainerInterface    $container
     * @param null|\Psr\SimpleCache\CacheInterface $cache
     *
     * @return null|\Psr\SimpleCache\CacheInterface
     */
    public static function extendSimpleTraceableCache(
        ContainerInterface $container,
        ?CacheInterface $cache = null
    ): ?CacheInterface {
        if ($cache !== null) {
            if ($cache instanceof PhpCachePoolInterface) {
                return new PhpCacheTraceableCacheDecorator($cache);
            }

            return new SimpleTraceableCacheDecorator($cache);
        }

        return $cache;
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Psr\Container\ContainerInterface                  $container
     * @param null|\Viserio\Component\Contract\Profiler\Profiler $profiler
     *
     * @return null|\Viserio\Component\Contract\Profiler\Profiler
     */
    public static function extendProfiler(
        ContainerInterface $container,
        ?ProfilerContract $profiler = null
    ): ?ProfilerContract {
        if ($profiler !== null) {
            $collector = new Psr6Psr16CacheDataCollector();

            if ($container->has(CacheItemPoolInterface::class)) {
                if (($cache = $container->get(CacheItemPoolInterface::class)) instanceof TraceableCacheItemDecorator) {
                    $collector->addPool($cache);
                }
            }

            if ($container->has(CacheInterface::class)) {
                $cache = $container->get(CacheInterface::class);

                if ($cache instanceof SimpleTraceableCacheDecorator || $cache instanceof PhpCacheTraceableCacheDecorator) {
                    $collector->addPool($cache);
                }
            }

            $profiler->addCollector($collector);
        }

        return $profiler;
    }
}
