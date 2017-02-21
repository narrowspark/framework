<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Cache\Manager;
use Viserio\Component\Contracts\Cache\Traits\CacheManagerAwareTrait;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;

class CacheManagerAwareTraitTest extends MockeryTestCase
{
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
