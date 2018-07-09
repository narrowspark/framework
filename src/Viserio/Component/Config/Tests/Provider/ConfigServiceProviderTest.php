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

/**
 * @internal
 */
final class ConfigServiceProviderTest extends TestCase
{
    public function testGetFactories(): void
    {
        $container = new Container();
        $container->instance(LoaderContract::class, new FileLoader());
        $container->register(new ConfigServiceProvider());

        $config = $container->get(RepositoryContract::class);
        $config->set('foo', 'bar');
        $alias = $container->get('config');

        static::assertInstanceOf(Repository::class, $container->get(RepositoryContract::class));
        static::assertInstanceOf(Repository::class, $container->get(Repository::class));
        static::assertEquals($config, $alias);
        static::assertTrue($config->has('foo'));
        static::assertSame('bar', $config->get('foo'));
        static::assertInstanceOf(LoaderContract::class, $container->get(RepositoryContract::class)->getLoader());
    }
}
