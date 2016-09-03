<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests\Providers;

use Viserio\Container\Container;
use Viserio\StaticalProxy\AliasLoader;
use Viserio\StaticalProxy\Providers\AliasLoaderServiceProvider;

class AliasLoaderServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new AliasLoaderServiceProvider());

        $this->assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
        $this->assertInstanceOf(AliasLoader::class, $container->get('alias'));
    }
}
