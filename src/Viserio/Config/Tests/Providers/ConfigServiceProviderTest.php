<?php
declare(strict_types=1);
namespace Viserio\Config\Tests\Providers;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Config\Repository;
use Viserio\Container\Container;

class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());

        $config = $container->get(ConfigManager::class);
        $config->set('foo', 'bar');
        $alias = $container->get('config');

        $this->assertInstanceOf(Repository::class, $container->get(Repository::class));
        $this->assertInstanceOf(ConfigManager::class, $config);
        $this->assertEquals($config, $alias);
        $this->assertTrue($config->has('foo'));
        $this->assertTrue($alias->has('foo'));
        $this->assertSame('bar', $config->get('foo'));
    }
}
