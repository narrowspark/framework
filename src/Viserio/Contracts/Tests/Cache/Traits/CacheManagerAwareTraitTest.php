<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Cache\Manager;
use Viserio\Contracts\Cache\Traits\CacheManagerAwareTrait;
use PHPUnit\Framework\TestCase;

class CacheManagerAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use CacheManagerAwareTrait;

    public function testGetAndSetCache()
    {
        $this->setCacheManager($this->mock(Manager::class));

        self::assertInstanceOf(Manager::class, $this->getCacheManager());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cache Manager is not set up.
     */
    public function testGetCacheThrowExceptionIfCacheIsNotSet()
    {
        $this->getCacheManager();
    }
}
