<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Cache\Manager;
use Viserio\Component\Contract\Cache\Traits\CacheManagerAwareTrait;

class CacheManagerAwareTraitTest extends MockeryTestCase
{
    use CacheManagerAwareTrait;

    public function testGetAndSetCache(): void
    {
        $this->setCacheManager($this->mock(Manager::class));

        self::assertInstanceOf(Manager::class, $this->getCacheManager());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cache Manager is not set up.
     */
    public function testGetCacheThrowExceptionIfCacheIsNotSet(): void
    {
        $this->getCacheManager();
    }
}
