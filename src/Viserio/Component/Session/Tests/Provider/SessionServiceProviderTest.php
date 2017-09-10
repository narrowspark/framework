<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Provider;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Encryption\Provider\EncrypterServiceProvider;
use Viserio\Component\Filesystem\Provider\FilesServiceProvider;
use Viserio\Component\Session\Provider\SessionServiceProvider;
use Viserio\Component\Session\SessionManager;

class SessionServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new EncrypterServiceProvider());
        $container->register(new SessionServiceProvider());
        $container->register(new FilesServiceProvider());

        $password = \random_bytes(32);

        $container->instance('config', [
            'viserio' => [
                'session' => [
                    'default'  => 'file',
                    'drivers'  => [
                        'file' => [
                            'path' => '',
                        ],
                    ],
                    'lifetime' => 3000,
                    'cookie'   => 'test',
                ],
                'encryption' => [
                    'key' => KeyFactory::exportToHiddenString(KeyFactory::generateKey($password)),
                ],
            ],
        ]);

        self::assertInstanceOf(SessionManager::class, $container->get(SessionManager::class));
        self::assertInstanceOf(SessionManager::class, $container->get('session'));
        self::assertInstanceOf(StoreContract::class, $container->get('session.store'));
    }
}
