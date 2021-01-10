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

namespace Viserio\Provider\Framework\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Provider\Framework\Bootstrap\LoadServiceProviderBootstrap;
use Viserio\Provider\Framework\Tests\Fixture\Provider\FixtureServiceProvider;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
            ->with('viserio.container.dumper.preload_classes', []);

        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('getRegisteredServiceProviders')
            ->andReturn(require \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'serviceproviders.php');

        LoadServiceProviderBootstrap::bootstrap($kernel);
    }
}
