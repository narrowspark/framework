<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests\Provider;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Encryption\Password;
use Viserio\Component\Encryption\Provider\HashingServiceProvider;

class HashingServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new HashingServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'hashing' => [
                    'key' => Key::createNewRandomKey()->saveToAsciiSafeString(),
                ],
            ],
        ]);

        self::assertInstanceOf(Password::class, $container->get(Password::class));
        self::assertInstanceOf(Password::class, $container->get('password'));
    }
}
