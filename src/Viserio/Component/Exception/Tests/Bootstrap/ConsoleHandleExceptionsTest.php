<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Exception\Bootstrap\ConsoleHandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;

/**
 * @internal
 */
final class ConsoleHandleExceptionsTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        $this->assertSame(64, ConsoleHandleExceptions::getPriority());
    }

    public function testGetType(): void
    {
        $this->assertSame(BootstrapStateContract::TYPE_AFTER, ConsoleHandleExceptions::getType());
    }

    public function testGetBootstrapper(): void
    {
        $this->assertSame(LoadServiceProvider::class, ConsoleHandleExceptions::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $handler = $this->mock(ConsoleHandlerContract::class);
        $handler->shouldReceive('register')
            ->once();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->once()
            ->with(ConsoleHandlerContract::class)
            ->andReturn($handler);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        ConsoleHandleExceptions::bootstrap($kernel);
    }
}
