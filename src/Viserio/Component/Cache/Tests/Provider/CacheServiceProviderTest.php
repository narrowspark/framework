<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Tests\Provider;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Cache\Provider\CacheServiceProvider;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;

/**
 * @internal
 */
final class CacheServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new CacheServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->get(RepositoryContract::class)->setArray([
            'viserio' => [
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ],
        ]);

        static::assertInstanceOf(CacheManager::class, $container->get(CacheManager::class));
        static::assertInstanceOf(CacheManager::class, $container->get('cache'));

        static::assertInstanceOf(ArrayCachePool::class, $container->get('cache.store'));
        static::assertInstanceOf(CacheItemPoolInterface::class, $container->get('cache.store'));
        static::assertInstanceOf(CacheItemPoolInterface::class, $container->get(CacheItemPoolInterface::class));
    }
}
