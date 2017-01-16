<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Tests\Providers;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Cache\Providers\CacheServiceProvider;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;

class CacheServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new CacheServiceProvider());
        $container->register(new ConfigServiceProvider());

        self::assertInstanceOf(CacheManager::class, $container->get(CacheManager::class));
        self::assertInstanceOf(CacheManager::class, $container->get('cache'));

        self::assertInstanceOf(ArrayCachePool::class, $container->get('cache.store'));
        self::assertInstanceOf(CacheItemPoolInterface::class, $container->get('cache.store'));
        self::assertInstanceOf(CacheItemPoolInterface::class, $container->get(CacheItemPoolInterface::class));
    }
}
