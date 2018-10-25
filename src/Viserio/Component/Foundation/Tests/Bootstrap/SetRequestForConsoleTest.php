<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsole;

/**
 * @internal
 */
final class SetRequestForConsoleTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        static::assertSame(256, SetRequestForConsole::getPriority());
    }

    public function testBootstrap(): void
    {
        $kernelMock = $this->mock(KernelContract::class);

        $kernelMock->shouldReceive('getKernelConfigurations')
            ->once()
            ->andReturn(['url' => 'localhost']);

        $kernelMock->shouldReceive('getContainer->register')
            ->once();

        SetRequestForConsole::bootstrap($kernelMock);
    }
}
