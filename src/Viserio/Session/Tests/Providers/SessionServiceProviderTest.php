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

        $container->get('config')->set('encrypter', [
            'key' => $key->saveToAsciiSafeString(),
        ]);
        $container->get('config')->set('session', [
            'path' => '',
            'lifetime' => '',
        ]);

        $this->assertInstanceOf(SessionManager::class, $container->get(SessionManager::class));
        $this->assertInstanceOf(SessionManager::class, $container->get('session'));
        // $this->assertInstanceOf(StoreContract::class, $container->get('session.store'));
    }
}
