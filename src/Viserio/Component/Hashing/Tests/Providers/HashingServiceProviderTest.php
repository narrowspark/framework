<?php
declare(strict_types=1);
namespace Viserio\Component\Hashing\Tests\Providers;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Hashing\Password;
use Viserio\Component\Hashing\Providers\HashingServiceProvider;

class HashingServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new HashingServiceProvider());

        $container->get(RepositoryContract::class)->set('hashing', [
            'key' => Key::createNewRandomKey(),
        ]);

        self::assertInstanceOf(Password::class, $container->get(Password::class));
        self::assertInstanceOf(Password::class, $container->get('password'));
    }

    public function testProviderWithoutRepositoryContract()
    {
        $container = new Container();
        $container->register(new HashingServiceProvider());

        $container->instance('options', [
            'key' => Key::createNewRandomKey(),
        ]);

        self::assertInstanceOf(Password::class, $container->get(Password::class));
        self::assertInstanceOf(Password::class, $container->get('password'));
    }

    public function testProviderWithoutRepositoryContractAndNamespace()
    {
        $container = new Container();
        $container->register(new HashingServiceProvider());

        $container->instance('viserio.hashing.options', [
            'key' => Key::createNewRandomKey(),
        ]);

        self::assertInstanceOf(Password::class, $container->get(Password::class));
        self::assertInstanceOf(Password::class, $container->get('password'));
    }
}
