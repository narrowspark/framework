<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsoleBootstrap;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition;
use Viserio\Contract\Foundation\Kernel as KernelContract;

interface DummyContainer extends ContainerBuilderContract
{
}

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class SetRequestForConsoleBootstrapTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(128, SetRequestForConsoleBootstrap::getPriority());
    }

    public function testBootstrap(): void
    {
        $kernelMock = Mockery::mock(KernelContract::class);

        $definitionMock = Mockery::mock(ObjectDefinition::class);
        $definitionMock->shouldReceive('setArguments')
            ->once();

        $container = Mockery::mock(DummyContainer::class);
        $container->shouldReceive('singleton')
            ->once()
            ->with(ServerRequestInterface::class, Mockery::type('array'))
            ->andReturn($definitionMock);

        $kernelMock->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($container);

        SetRequestForConsoleBootstrap::bootstrap($kernelMock);
    }
}
