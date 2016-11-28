<?php
declare(strict_types=1);
namespace Viserio\Cache\Tests\Providers;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Cache\CacheManager;
use Viserio\Cache\Providers\CacheServiceProvider;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;

class CacheServiceProviderTest extends \PHPUnit_Framework_TestCase
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
