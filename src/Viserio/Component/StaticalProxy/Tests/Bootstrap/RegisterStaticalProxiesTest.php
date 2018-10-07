<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Component\StaticalProxy\Bootstrap\RegisterStaticalProxies;

/**
 * @internal
 */
final class RegisterStaticalProxiesTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $aliasLoader = $this->mock(AliasLoaderContract::class);
        $aliasLoader->shouldReceive('register')
            ->once();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->once()
            ->with(AliasLoaderContract::class)
            ->andReturn($aliasLoader);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        RegisterStaticalProxies::bootstrap($kernel);
    }
}
