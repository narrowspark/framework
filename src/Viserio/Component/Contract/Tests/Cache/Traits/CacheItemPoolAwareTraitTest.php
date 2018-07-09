<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contract\Cache\Traits\CacheItemPoolAwareTrait;

/**
 * @internal
 */
final class CacheItemPoolAwareTraitTest extends MockeryTestCase
{
    use CacheItemPoolAwareTrait;

    public function testGetAndSetCache(): void
    {
        $this->setCacheItemPool($this->mock(CacheItemPoolInterface::class));

        static::assertInstanceOf(CacheItemPoolInterface::class, $this->getCacheItemPool());
    }

    public function testGetCacheItemPoolThrowExceptionIfCacheItemPoolIsNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Instance implementing [\\Psr\\Cache\\CacheItemPoolInterface] is not set up.');

        $this->getCacheItemPool();
    }
}
