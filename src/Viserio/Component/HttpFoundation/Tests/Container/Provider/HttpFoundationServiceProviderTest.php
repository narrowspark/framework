<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\HttpFoundation\Tests\Provider;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\HttpFoundation\Console\Command\DownCommand;
use Viserio\Component\HttpFoundation\Console\Command\UpCommand;
use Viserio\Component\HttpFoundation\Container\Provider\HttpFoundationServiceProvider;
use Viserio\Contract\Foundation\Kernel as ContractKernel;

/**
 * @internal
 *
 * @small
 */
final class HttpFoundationServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    public function testProvider(): void
    {
        $kernel = Mockery::mock(ContractKernel::class);
        $kernel->shouldReceive('getRootDir')
            ->once()
            ->andReturn(__DIR__);

        $this->container->set(ContractKernel::class, $kernel);

        self::assertInstanceOf(SourceContextProvider::class, $this->container->get(SourceContextProvider::class));
        self::assertInstanceOf(SourceContextProvider::class, $this->container->get(ContextProviderInterface::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(UpCommand::getDefaultName()));
        self::assertTrue($console->has(DownCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('viserio', [
            'app' => [
                'charset' => 'UTF-8',
            ],
            'console' => [
                'name' => 'test',
                'version' => '1',
            ],
        ]);

        $containerBuilder->singleton(ContractKernel::class)
            ->setSynthetic(true);
        $containerBuilder->register(new ConfigServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new HttpFoundationServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function assertPostConditions(): void
    {
        $this->mockeryAssertPostConditions();
    }
}
