<?php
declare(strict_types=1);
namespace Viserio\Config\Tests\Providers;

use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Config\Repository;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Container\Container;

class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());

        $config = $container->get(RepositoryContract::class);
        $config->set('foo', 'bar');
        $alias = $container->get('config');

        self::assertInstanceOf(Repository::class, $container->get(RepositoryContract::class));
        self::assertInstanceOf(Repository::class, $container->get(Repository::class));
        self::assertEquals($config, $alias);
        self::assertTrue($config->has('foo'));
        self::assertTrue($alias->has('foo'));
        self::assertSame('bar', $config->get('foo'));
    }
}
