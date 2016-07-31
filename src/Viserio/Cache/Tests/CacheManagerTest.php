<?php
declare(strict_types=1);
namespace Viserio\Cache\Tests;

use Viserio\Cache\CacheManager;
use Viserio\Contracts\Config\Manager as ConfigManager;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->manager = new CacheManager(
            $this->mock(ConfigManager::class)
        );
    }

    public function testArrayPoolCall()
    {
        $this->assertInstanceOf(, $this->driver('array'));
    }
}
