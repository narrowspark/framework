<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\StaticalProxy\AliasLoader;
use Viserio\StaticalProxy\Providers\AliasLoaderServiceProvider;

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
