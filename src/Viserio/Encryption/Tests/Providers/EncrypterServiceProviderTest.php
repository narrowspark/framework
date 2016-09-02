<?php
declare(strict_types=1);
namespace Viserio\Encryption\Tests\Providers;

use Defuse\Crypto\Key;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Encryption\Encrypter;
use Viserio\Encryption\Providers\EncrypterServiceProvider;

class EncrypterServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new EncrypterServiceProvider());

        $key = Key::createNewRandomKey();

        $container->get('config')->set('encrypter', [
            'key' => $key->saveToAsciiSafeString(),
        ]);

        $this->assertInstanceOf(Encrypter::class, $container->get(Encrypter::class));
        $this->assertInstanceOf(Encrypter::class, $container->get('encrypter'));
    }
}
