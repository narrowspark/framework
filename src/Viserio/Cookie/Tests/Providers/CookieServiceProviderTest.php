<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\RequestCookie;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Cookie\Providers\CookieServiceProvider;

class CookieServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new CookieServiceProvider());

        $container->get('config')->set('session', [
            'domain' => '',
            'path' => '',
            'secure' => true,
        ]);

        $this->assertInstanceOf(RequestCookie::class, $container->get(RequestCookie::class));
        $this->assertInstanceOf(CookieJar::class, $container->get(CookieJar::class));
    }
}
