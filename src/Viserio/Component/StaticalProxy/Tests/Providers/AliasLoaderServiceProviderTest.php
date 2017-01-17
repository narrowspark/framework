<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\StaticalProxy\AliasLoader;
use Viserio\Component\StaticalProxy\Providers\AliasLoaderServiceProvider;

class AliasLoaderServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new AliasLoaderServiceProvider());

        $container->get('config')->set('aliasloader', [
            'aliases' => [],
        ]);

        self::assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
        self::assertInstanceOf(AliasLoader::class, $container->get('alias'));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new AliasLoaderServiceProvider());

        $container->instance('options', [
            'aliases' => [],
        ]);

        self::assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new AliasLoaderServiceProvider());

        $container->instance('viserio.staticalproxy.options', [
            'aliases' => [],
        ]);

        self::assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
    }
}
