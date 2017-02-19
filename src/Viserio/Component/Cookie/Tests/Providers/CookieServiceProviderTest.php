<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\Cookie\Providers\CookieServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class CookieServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new CookieServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'cookie' => [
                    'domain' => '',
                    'path'   => '',
                    'secure' => true,
                ],
            ],
        ]);

        self::assertInstanceOf(CookieJar::class, $container->get(CookieJar::class));
    }
}
