<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Cache\Manager;
use Viserio\Contracts\Cache\Traits\CacheAwareTrait;

class CacheAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use CacheAwareTrait;

    public function testGetAndSetCache()
    {
        $this->setCacheManager($this->mock(Manager::class));

        $this->assertInstanceOf(Manager::class, $this->getCacheManager());
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
