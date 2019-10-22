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

namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProviderBootstrap;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsoleBootstrap;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

interface DummyContainer extends ContainerBuilderContract
{
}

/**
 * @internal
 *
 * @small
 */
final class SetRequestForConsoleBootstrapTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(64, SetRequestForConsoleBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, SetRequestForConsoleBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(LoadServiceProviderBootstrap::class, SetRequestForConsoleBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $kernelMock = \Mockery::mock(KernelContract::class);

        $definitionMock = \Mockery::mock(ObjectDefinition::class);
        $definitionMock->shouldReceive('setArguments')
            ->once();

        $container = \Mockery::mock(DummyContainer::class);
        $container->shouldReceive('singleton')
            ->once()
            ->with(ServerRequestInterface::class, \Mockery::type('array'))
            ->andReturn($definitionMock);

        $kernelMock->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($container);

        SetRequestForConsoleBootstrap::bootstrap($kernelMock);
    }
}
