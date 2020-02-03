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

namespace Viserio\Component\Parser\Tests\Provider;

use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Parser\Command\XliffLintCommand;
use Viserio\Component\Parser\Command\YamlLintCommand;
use Viserio\Component\Parser\Container\Provider\ConsoleCommandsServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class ConsoleCommandsServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(XliffLintCommand::class, $this->container->get(XliffLintCommand::class));
        self::assertInstanceOf(YamlLintCommand::class, $this->container->get(YamlLintCommand::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has('lint:xliff'));
        self::assertTrue($console->has('lint:yaml'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ConsoleServiceProvider());
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
