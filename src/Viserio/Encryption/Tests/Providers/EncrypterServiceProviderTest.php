<?php
declare(strict_types=1);
namespace Viserio\Encryption\Tests\Providers;

use Defuse\Crypto\Key;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Encryption\Encrypter;
use Viserio\Encryption\Providers\EncrypterServiceProvider;
use PHPUnit\Framework\TestCase;

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
