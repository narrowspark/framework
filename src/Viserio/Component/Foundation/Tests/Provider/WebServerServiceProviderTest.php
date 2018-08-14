<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Foundation\Kernel as ContractKernel;
use Viserio\Component\Foundation\Provider\WebServerServiceProvider;

/**
 * @internal
 */
final class WebServerServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $kernel = $this->mock(ContractKernel::class);
        $kernel->shouldReceive('getRootDir')
            ->once()
            ->andReturn(__DIR__);

        $container = new Container();
        $container->register(new WebServerServiceProvider());
        $container->instance(ContractKernel::class, $kernel);

        static::assertInstanceOf(SourceContextProvider::class, $container->get(SourceContextProvider::class));
    }
}
