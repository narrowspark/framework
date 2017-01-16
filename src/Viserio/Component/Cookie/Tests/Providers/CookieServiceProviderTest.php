<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\Cookie\Providers\CookieServiceProvider;

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
