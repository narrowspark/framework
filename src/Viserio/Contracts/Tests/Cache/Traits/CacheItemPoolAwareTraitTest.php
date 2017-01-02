<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Contracts\Cache\Traits\CacheItemPoolAwareTrait;

class CacheItemPoolAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use CacheItemPoolAwareTrait;

    public function testGetAndSetCache()
    {
        $this->setCacheItemPool($this->mock(CacheItemPoolInterface::class));

        self::assertInstanceOf(CacheItemPoolInterface::class, $this->getCacheItemPool());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing \Psr\Cache\CacheItemPoolInterface is not set up.
     */
    public function testGetCacheItemPoolThrowExceptionIfCacheItemPoolIsNotSet()
    {
        $this->getCacheItemPool();
    }
}
