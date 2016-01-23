<?php
namespace Viserio\Cache\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cache\CacheManager;
use Viserio\Contracts\Config\Manager as ConfigContract;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected $manager;

    public function setUp()
    {
        $config = $this->mock(ConfigContract::class);

        $this->manager = new CacheManager($config);
    }
}
