<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests\Provider;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\Provider\EncrypterServiceProvider;

class EncrypterServiceProviderTest extends TestCase
{
    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new EncrypterServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'encryption' => [
                    'key' => Key::createNewRandomKey()->saveToAsciiSafeString(),
                ],
            ],
        ]);

        self::assertInstanceOf(Encrypter::class, $container->get(Encrypter::class));
        self::assertInstanceOf(Encrypter::class, $container->get('encrypter'));
    }
}
