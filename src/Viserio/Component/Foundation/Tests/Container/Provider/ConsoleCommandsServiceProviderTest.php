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

namespace Viserio\Component\Foundation\Tests\Container\Provider;

use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Foundation\Config\Command\ConfigCacheCommand;
use Viserio\Component\Foundation\Config\Command\ConfigClearCommand;
use Viserio\Component\Foundation\Container\Provider\ConsoleCommandsServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class ConsoleCommandsServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(ConfigCacheCommand::class, $this->container->get(ConfigCacheCommand::class));
        self::assertInstanceOf(ConfigClearCommand::class, $this->container->get(ConfigClearCommand::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(ConfigCacheCommand::getDefaultName()));
        self::assertTrue($console->has(ConfigClearCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new ConfigServiceProvider());
        $containerBuilder->register(new ConsoleCommandsServiceProvider());
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
}
