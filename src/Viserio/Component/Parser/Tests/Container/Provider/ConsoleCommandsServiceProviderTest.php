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
 * @coversNothing
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
