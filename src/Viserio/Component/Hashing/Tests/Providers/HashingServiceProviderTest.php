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
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class HashingServiceProviderTest extends TestCase
{
    public function testProviderWithoutRepositoryContract()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new HashingServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'hashing' => [
                    'key' => Key::createNewRandomKey()->saveToAsciiSafeString(),
                ]
            ]
        ]);

        self::assertInstanceOf(Password::class, $container->get(Password::class));
        self::assertInstanceOf(Password::class, $container->get('password'));
    }
}
