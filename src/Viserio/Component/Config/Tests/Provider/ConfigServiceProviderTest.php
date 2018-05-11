<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Config\Repository;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;
use Viserio\Component\Parser\FileLoader;

class ConfigServiceProviderTest extends TestCase
{
    public function testGetFactories(): void
    {
        $container = new Container();
        $container->instance(LoaderContract::class, new FileLoader());
        $container->register(new ConfigServiceProvider());

        $config = $container->get(RepositoryContract::class);
        $config->set('foo', 'bar');
        $alias = $container->get('config');

        self::assertInstanceOf(Repository::class, $container->get(RepositoryContract::class));
        self::assertInstanceOf(Repository::class, $container->get(Repository::class));
        self::assertEquals($config, $alias);
        self::assertTrue($config->has('foo'));
        self::assertSame('bar', $config->get('foo'));
        self::assertInstanceOf(LoaderContract::class, $container->get(RepositoryContract::class)->getLoader());
    }
}
