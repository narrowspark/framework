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
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\HttpFoundation\Console\Command\DownCommand;
use Viserio\Component\HttpFoundation\Console\Command\UpCommand;
use Viserio\Component\HttpFoundation\Container\Provider\WebServerServiceProvider;
use Viserio\Contract\Foundation\Kernel as ContractKernel;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 *
 * @small
 */
final class WebServerServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetExtensions(): void
    {
        $kernel = Mockery::mock(ContractKernel::class);

        $this->container->set(ContractKernel::class, $kernel);

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(UpCommand::getDefaultName()));
        self::assertTrue($console->has(DownCommand::getDefaultName()));

        $this->assertDumpedContainer(null);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new WebServerServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Compiled';
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
