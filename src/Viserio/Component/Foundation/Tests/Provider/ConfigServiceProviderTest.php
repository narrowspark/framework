<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Foundation\Provider\ConfigServiceProvider as FoundationConfigServiceProvider;

/**
 * @internal
 */
final class ConfigServiceProviderTest extends MockeryTestCase
{
    public function testGetExtensions(): void
    {
        $kernelMock = $this->mock(KernelContract::class);
        $kernelMock->shouldReceive('getRootDir')
            ->once()
            ->andReturn(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture');

        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new FoundationConfigServiceProvider());
        $container->instance(KernelContract::class, $kernelMock);

        $processors = $container->get(RepositoryContract::class)->getParameterProcessors();

        static::assertCount(2, $processors);
        static::assertInstanceOf(EnvParameterProcessor::class, $processors['env']);
        static::assertInstanceOf(ComposerExtraProcessor::class, $processors['composer-extra']);
    }
}
