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
        $config->set('factory_test', 'bar');
        $alias = $container->get('config');

        $this->assertInstanceOf(Repository::class, $container->get(RepositoryContract::class));
        $this->assertInstanceOf(Repository::class, $container->get(Repository::class));
        $this->assertEquals($config, $alias);
        $this->assertTrue($config->has('factory_test'));
        $this->assertSame('bar', $config->get('factory_test'));
        $this->assertInstanceOf(LoaderContract::class, $container->get(RepositoryContract::class)->getLoader());
    }
}
