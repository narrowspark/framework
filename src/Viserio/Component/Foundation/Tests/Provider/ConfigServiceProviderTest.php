<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Config\Repository;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;
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
        $container->instance(KernelContract::class, $kernelMock);

        $repo = new Repository();
        $repo->setArray(
            [
                'viserio' => [
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
                ],
            ]
        );
        $container->instance(RepositoryContract::class, $repo);
        $container->register(new FoundationConfigServiceProvider());

        $processors = $container->get(RepositoryContract::class)->getParameterProcessors();

        $this->assertCount(2, $processors);
        $this->assertInstanceOf(ComposerExtraProcessor::class, $processors['composer-extra']);
        $this->assertInstanceOf(DirectoryProcessor::class, $processors['directory']);
    }
}
