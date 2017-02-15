<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests\Providers;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\Providers\EncrypterServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class EncrypterServiceProviderTest extends TestCase
{
    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new EncrypterServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'encryption' => [
                    'key' => Key::createNewRandomKey()->saveToAsciiSafeString(),
                ]
            ]
        ]);

        self::assertInstanceOf(Encrypter::class, $container->get(Encrypter::class));
        self::assertInstanceOf(Encrypter::class, $container->get('encrypter'));
    }
}
