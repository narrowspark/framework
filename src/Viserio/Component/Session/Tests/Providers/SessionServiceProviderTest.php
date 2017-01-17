<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Providers;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Encryption\Providers\EncrypterServiceProvider;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;
use Viserio\Component\Session\Providers\SessionServiceProvider;
use Viserio\Component\Session\SessionManager;

class SessionServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new EncrypterServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new SessionServiceProvider());
        $container->register(new FilesServiceProvider());

        $key = Key::createNewRandomKey();

        $container->get('config')->set('encryption', [
            'key' => $key->saveToAsciiSafeString(),
        ]);
        $container->get('config')->set('session', [
            'path'     => '',
            'lifetime' => 3000,
            'cookie'   => 'test',
        ]);

        self::assertInstanceOf(SessionManager::class, $container->get(SessionManager::class));
        self::assertInstanceOf(SessionManager::class, $container->get('session'));
        self::assertInstanceOf(StoreContract::class, $container->get('session.store'));
    }
}
