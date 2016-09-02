<?php
declare(strict_types=1);
namespace Viserio\Hashing\Tests\Providers;

use Defuse\Crypto\Key;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Hashing\Password;
use Viserio\Hashing\Providers\HashingServiceProvider;

class HashingServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new HashingServiceProvider());

        $container->get(ConfigManager::class)->set('hashing', [
            'key' => Key::createNewRandomKey(),
        ]);

        $this->assertInstanceOf(Password::class, $container->get(Password::class));
        $this->assertInstanceOf(Password::class, $container->get('password'));
    }
}
