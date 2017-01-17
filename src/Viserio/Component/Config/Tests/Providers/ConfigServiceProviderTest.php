<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Config\Repository;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

class ConfigServiceProviderTest extends TestCase
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
