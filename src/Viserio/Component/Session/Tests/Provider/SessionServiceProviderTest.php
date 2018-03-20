<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Provider;

use ParagonIE\Halite\KeyFactory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Session\Provider\SessionServiceProvider;
use Viserio\Component\Session\SessionManager;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class SessionServiceProviderTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new SessionServiceProvider());

        $path = self::normalizeDirectorySeparator(__DIR__ . '/test_key');

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $path);

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
