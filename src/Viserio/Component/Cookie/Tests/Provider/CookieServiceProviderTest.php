<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\Cookie\Provider\CookieServiceProvider;

/**
 * @internal
 */
final class CookieServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
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

        $this->assertInstanceOf(CookieJar::class, $container->get(CookieJar::class));
    }
}
