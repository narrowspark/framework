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

namespace Viserio\Provider\Framework\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Provider\Framework\Bootstrap\ConfigurationLoaderBootstrap;
use Viserio\Provider\Framework\Bootstrap\LoadServiceProviderBootstrap;

/**
 * @internal
 *
 * @small
 */
final class ConfigurationLoaderBootstrapTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(64, ConfigurationLoaderBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, ConfigurationLoaderBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(LoadServiceProviderBootstrap::class, ConfigurationLoaderBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $container = $this->mock(ContainerBuilderContract::class);

        $env = 'dev';
        $fixtureDir = dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture';

        $container->shouldReceive('hasParameter')
            ->once()
            ->with('viserio')
            ->andReturn(false);
        $container->shouldReceive('hasParameter')
            ->once()
            ->with('bar')
            ->andReturn(true);
        $container->shouldReceive('hasParameter')
            ->once()
            ->with('foo.bar.baz')
            ->andReturn(false);

        $container->shouldReceive('setParameter')
            ->once()
            ->with('viserio', [
                'framework' => [
                    'test' => 'foo',
                ],
            ]);
        $container->shouldReceive('getParameter')
            ->once()
            ->with('bar')
            ->andReturn(new ParameterDefinition('bar', 'test'));
        $container->shouldReceive('setParameter')
            ->once()
            ->with('bar', 'foo');
        $container->shouldReceive('setParameter')
            ->once()
            ->with('foo.bar.baz', 'bar');

        $kernel = $this->mock(KernelContract::class);

        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('packages')
            ->andReturn($fixtureDir . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'packages');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->andReturn($fixtureDir . \DIRECTORY_SEPARATOR . 'config');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with($env)
            ->andReturn($fixtureDir . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . $env);
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('packages' . \DIRECTORY_SEPARATOR . $env)
            ->andReturn($fixtureDir . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'packages' . \DIRECTORY_SEPARATOR . $env);

        $kernel->shouldReceive('getEnvironment')
            ->once()
            ->andReturn($env);
        $kernel->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($container);

        ConfigurationLoaderBootstrap::bootstrap($kernel);
    }
}
