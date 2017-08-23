<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Encryption\Provider\EncrypterServiceProvider;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\Contracts\Encryption\Password as PasswordContract;
use Viserio\Component\Encryption\Password;

class EncrypterServiceProviderTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new EncrypterServiceProvider());


        $password = \random_bytes(32);

        $container->instance('config', [
            'viserio' => [
                'encryption' => [
                    'key' => KeyFactory::exportToHiddenString(KeyFactory::generateKey($password)),
                ],
            ],
        ]);

        self::assertInstanceOf(Encrypter::class, $container->get(Encrypter::class));
        self::assertInstanceOf(Encrypter::class, $container->get('encrypter'));
        self::assertInstanceOf(Password::class, $container->get(Password::class));
        self::assertInstanceOf(Password::class, $container->get('password'));
    }
}
