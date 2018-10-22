<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Provider;

use ParagonIE\Halite\KeyFactory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Session\Provider\SessionServiceProvider;
use Viserio\Component\Session\SessionManager;

/**
 * @internal
 */
final class SessionServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new SessionServiceProvider());

        $path = __DIR__ . \DIRECTORY_SEPARATOR . 'test_key';

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $path);

        $container->instance('config', [
            'viserio' => [
                'session' => [
                    'default'  => 'file',
                    'env'      => 'local',
                    'lifetime' => 3000,
                    'key_path' => $path,
                    'drivers'  => [
                        'file' => [
                            'path' => __DIR__ . \DIRECTORY_SEPARATOR . 'session',
                        ],
                    ],
                ],
            ],
        ]);

        static::assertInstanceOf(SessionManager::class, $container->get(SessionManager::class));
        static::assertInstanceOf(SessionManager::class, $container->get('session'));
        static::assertInstanceOf(StoreContract::class, $container->get('session.store'));

        \unlink($path);
    }
}
