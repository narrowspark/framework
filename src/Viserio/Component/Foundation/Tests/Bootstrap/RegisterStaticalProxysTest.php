<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Component\Foundation\Bootstrap\RegisterStaticalProxys;

class RegisterStaticalProxysTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $bootstraper = new RegisterStaticalProxys();

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

        $bootstraper->bootstrap($kernel);
    }
}
