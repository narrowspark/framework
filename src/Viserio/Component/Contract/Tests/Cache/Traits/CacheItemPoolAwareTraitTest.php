<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contract\Cache\Traits\CacheItemPoolAwareTrait;

class CacheItemPoolAwareTraitTest extends MockeryTestCase
{
    use CacheItemPoolAwareTrait;

    public function testGetAndSetCache(): void
    {
        $this->setCacheItemPool($this->mock(CacheItemPoolInterface::class));

        self::assertInstanceOf(CacheItemPoolInterface::class, $this->getCacheItemPool());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing [\Psr\Cache\CacheItemPoolInterface] is not set up.
     */
    public function testGetCacheItemPoolThrowExceptionIfCacheItemPoolIsNotSet(): void
    {
        $this->getCacheItemPool();
    }
}
