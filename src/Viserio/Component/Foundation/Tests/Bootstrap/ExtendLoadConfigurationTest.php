<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\ExtendLoadConfiguration;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;

/**
 * @internal
 */
final class ExtendLoadConfigurationTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        $this->assertSame(64, ExtendLoadConfiguration::getPriority());
    }

    public function testGetType(): void
    {
        $this->assertSame(BootstrapStateContract::TYPE_BEFORE, ExtendLoadConfiguration::getType());
    }

    public function testGetBootstrapper(): void
    {
        $this->assertSame(LoadServiceProvider::class, ExtendLoadConfiguration::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $configMock = $this->mock(RepositoryContract::class);
        $configMock->shouldReceive('addParameterProcessor')
            ->once()
            ->with(\Mockery::type(ComposerExtraProcessor::class));
        $configMock->shouldReceive('offsetExists')
            ->once()
            ->andReturn(true);
        $configMock->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'config' => [
                    'processor' => [
                        'directory' => [
                            'mapper' => [
                                'config' => [
                                    AbstractKernel::class, 'getConfigPath',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        $configMock->shouldReceive('addParameterProcessor')
            ->once()
            ->with(\Mockery::type(DirectoryProcessor::class));

        $containerMock = $this->mock(ContainerContract::class);
        $containerMock->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($configMock);

        $kernelMock = $this->mock(KernelContract::class);
        $kernelMock->shouldReceive('getContainer')
            ->once()
            ->andReturn($containerMock);
        $kernelMock->shouldReceive('getRootDir')
            ->once()
            ->andReturn(__DIR__);

        (new ExtendLoadConfiguration())->bootstrap($kernelMock);
    }
}
