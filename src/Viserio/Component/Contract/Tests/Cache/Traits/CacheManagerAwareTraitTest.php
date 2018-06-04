<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Cache\Manager;
use Viserio\Component\Contract\Cache\Traits\CacheManagerAwareTrait;

/**
 * @internal
 */
final class CacheManagerAwareTraitTest extends MockeryTestCase
{
    use CacheManagerAwareTrait;

    public function testGetAndSetCache(): void
    {
        $this->setCacheManager($this->mock(Manager::class));

        $this->assertInstanceOf(Manager::class, $this->getCacheManager());
    }

    public function testGetCacheThrowExceptionIfCacheIsNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cache Manager is not set up.');

        $this->getCacheManager();
    }
}
