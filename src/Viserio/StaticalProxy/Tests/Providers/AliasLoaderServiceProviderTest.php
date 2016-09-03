<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests\Providers;

use Viserio\Container\Container;
use Viserio\StaticalProxy\AliasLoader;
use Viserio\StaticalProxy\Providers\AliasLoaderServiceProvider;
use Viserio\Config\Providers\ConfigServiceProvider;

class AliasLoaderServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new AliasLoaderServiceProvider());

        $container->get('config')->set('aliasloader', [
            'aliases' => [],
        ]);


        $this->assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
        $this->assertInstanceOf(AliasLoader::class, $container->get('alias'));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new AliasLoaderServiceProvider());

        $container->instance('options', [
            'aliases' => [],
        ]);

        $this->assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new AliasLoaderServiceProvider());

        $container->instance('viserio.staticalproxy.options', [
            'aliases' => [],
        ]);

        $this->assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
    }
}
