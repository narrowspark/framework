<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Session\Provider\SessionServiceProvider;
use Viserio\Component\Session\SessionManager;

class SessionServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new SessionServiceProvider());

        $path = __DIR__ . '/test_key';

        KeyFactory::saveKeyToFile($path, KeyFactory::generateKey());

        $container->instance('config', [
            'viserio' => [
                'session' => [
                    'default'  => 'file',
                    'lifetime' => 3000,
                    'key_path' => $path,
                ],
            ],
        ]);

        self::assertInstanceOf(SessionManager::class, $container->get(SessionManager::class));
        self::assertInstanceOf(SessionManager::class, $container->get('session'));
        self::assertInstanceOf(StoreContract::class, $container->get('session.store'));

        \unlink($path);
    }
}
