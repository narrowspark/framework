<?php
declare(strict_types=1);
namespace Viserio\Session\Tests\Providers;

use Defuse\Crypto\Key;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Encryption\Providers\EncrypterServiceProvider;
use Viserio\Filesystem\Providers\FilesServiceProvider;
use Viserio\Session\Providers\SessionServiceProvider;
use Viserio\Session\SessionManager;

class SessionServiceProviderTest extends \PHPUnit_Framework_TestCase
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
            'path' => '',
            'lifetime' => 3000,
            'cookie' => 'test',
        ]);

        self::assertInstanceOf(SessionManager::class, $container->get(SessionManager::class));
        self::assertInstanceOf(SessionManager::class, $container->get('session'));
        self::assertInstanceOf(StoreContract::class, $container->get('session.store'));
    }
}
