<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Cache\Adapter\Common\PhpCachePool as PhpCachePoolInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\PhpCacheTraceableCacheDecorator;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Psr6Psr16CacheDataCollector;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\SimpleTraceableCacheDecorator;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator;

class ProfilerPsr6Psr16CacheBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheItemPoolInterface::class => [self::class, 'createCacheItemPoolDecorator'],
            CacheInterface::class         => [self::class, 'createSimpleTraceableCacheDecorator'],
            ProfilerContract::class       => [self::class, 'extendsProfiler'],
        ];
    }

    /**
     * Decorate CacheItemPool instances.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Psr\Cache\CacheItemPoolInterface
     */
    public static function createCacheItemPoolDecorator(ContainerInterface $container, ?callable $getPrevious = null): ?CacheItemPoolInterface
    {
        $cache = \is_callable($getPrevious) ? $getPrevious() : $getPrevious;

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
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Psr\SimpleCache\CacheInterface
     */
    public static function createSimpleTraceableCacheDecorator(ContainerInterface $container, ?callable $getPrevious = null): ?CacheInterface
    {
        $cache = \is_callable($getPrevious) ? $getPrevious() : $getPrevious;

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
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function extendsProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = \is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($profiler !== null) {
            $collector = new Psr6Psr16CacheDataCollector();

            if ($container->has(CacheItemPoolInterface::class) || $container->has(CacheInterface::class)) {
                if (($cache = $container->get(CacheItemPoolInterface::class)) instanceof TraceableCacheItemDecorator ||
                    ($cache = $container->get(CacheInterface::class)) instanceof SimpleTraceableCacheDecorator ||
                    ($cache = $container->get(CacheInterface::class)) instanceof PhpCacheTraceableCacheDecorator
                ) {
                    $collector->addPool($cache);
                }
            }

            $profiler->addCollector($collector);
        }

        return $profiler;
    }
}
