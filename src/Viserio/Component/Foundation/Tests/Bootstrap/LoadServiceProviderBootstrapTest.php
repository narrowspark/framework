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

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProviderBootstrap;
use Viserio\Component\Foundation\Tests\Fixture\Provider\FixtureServiceProvider;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 *
 * @small
 */
final class LoadServiceProviderBootstrapTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(128, LoadServiceProviderBootstrap::getPriority());
    }

    public function testBootstrap(): void
    {
        $container = Mockery::mock(ContainerBuilderContract::class);
        $container->shouldReceive('register')
            ->once()
            ->with(Mockery::type(FixtureServiceProvider::class));
        $container->shouldReceive('setParameter')
            ->once()
            ->with('container.dumper.preload_classes', []);

        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('getRegisteredServiceProviders')
            ->andReturn(require \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'serviceproviders.php');

        LoadServiceProviderBootstrap::bootstrap($kernel);
    }
}
