<?php
declare(strict_types=1);
namespace Viserio\Cache\Tests\Providers;

use Viserio\Cache\CacheManager;
use Viserio\Cache\Providers\CacheServiceProvider;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;

class TwigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new CacheServiceProvider());
        $container->register(new ConfigServiceProvider());

        $this->assertInstanceOf(CacheManager::class, $container->get(CacheManager::class));
        $this->assertInstanceOf(CacheManager::class, $container->get('cache'));
    }
}
