<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests\Providers;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\Providers\EncrypterServiceProvider;

class EncrypterServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new EncrypterServiceProvider());

        $key = Key::createNewRandomKey();

        $container->get('config')->set('encryption', [
            'key' => $key->saveToAsciiSafeString(),
        ]);

        self::assertInstanceOf(Encrypter::class, $container->get(Encrypter::class));
        self::assertInstanceOf(Encrypter::class, $container->get('encrypter'));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new EncrypterServiceProvider());

        $key = Key::createNewRandomKey();

        $container->instance('options', [
            'key' => $key->saveToAsciiSafeString(),
        ]);

        self::assertInstanceOf(Encrypter::class, $container->get(Encrypter::class));
        self::assertInstanceOf(Encrypter::class, $container->get('encrypter'));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new EncrypterServiceProvider());

        $key = Key::createNewRandomKey();

        $container->instance('viserio.encryption.options', [
            'key' => $key->saveToAsciiSafeString(),
        ]);

        self::assertInstanceOf(Encrypter::class, $container->get(Encrypter::class));
        self::assertInstanceOf(Encrypter::class, $container->get('encrypter'));
    }
}
