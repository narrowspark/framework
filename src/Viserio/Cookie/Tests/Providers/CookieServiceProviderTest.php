<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests\Providers;

use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\Providers\CookieServiceProvider;
use PHPUnit\Framework\TestCase;

class CookieServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new CookieServiceProvider());

        $container->get('config')->set('cookie', [
            'domain' => '',
            'path'   => '',
            'secure' => true,
        ]);

        self::assertInstanceOf(CookieJar::class, $container->get(CookieJar::class));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new CookieServiceProvider());

        $container->instance('options', [
            'domain' => '',
            'path'   => '',
            'secure' => true,
        ]);

        self::assertInstanceOf(CookieJar::class, $container->get(CookieJar::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new CookieServiceProvider());

        $container->instance('viserio.cookie.options', [
            'domain' => '',
            'path'   => '',
            'secure' => true,
        ]);

        self::assertInstanceOf(CookieJar::class, $container->get(CookieJar::class));
    }
}
