<?php
declare(strict_types=1);
namespace Viserio\Config\Tests\Providers;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Config\Repository;
use Viserio\Container\Container;
use Viserio\Contracts\Config\Manager as ManagerContract;

class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());

        $config = $container->get(ConfigManager::class);
        $config->set('foo', 'bar');
        $alias = $container->get('config');

        self::assertInstanceOf(Repository::class, $container->get(Repository::class));
        self::assertInstanceOf(ConfigManager::class, $config);
        self::assertInstanceOf(ConfigManager::class, $container->get(ManagerContract::class));
        self::assertEquals($config, $alias);
        self::assertTrue($config->has('foo'));
        self::assertTrue($alias->has('foo'));
        self::assertSame('bar', $config->get('foo'));
    }
}
