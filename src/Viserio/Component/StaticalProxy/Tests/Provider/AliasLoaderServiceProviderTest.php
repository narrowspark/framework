<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\StaticalProxy\AliasLoader;
use Viserio\Component\StaticalProxy\Provider\AliasLoaderServiceProvider;

class AliasLoaderServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new AliasLoaderServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'staticalproxy' => [
                    'real_time_proxy' => true,
                    'cache_path'      => __DIR__,
                ],
            ],
        ]);

        self::assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
        self::assertInstanceOf(AliasLoader::class, $container->get('alias'));
    }

    public function testProviderWithKernelCachePath(): void
    {
        $container = new Container();
        $container->register(new AliasLoaderServiceProvider());
        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('staticalproxy')
            ->andReturn(__DIR__);

        $container->instance(KernelContract::class, $kernel);

        $container->instance('config', [
            'viserio' => [
                'staticalproxy' => [
                ],
            ],
        ]);

        self::assertInstanceOf(AliasLoader::class, $container->get(AliasLoader::class));
        self::assertInstanceOf(AliasLoader::class, $container->get('alias'));
    }
}
