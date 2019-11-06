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

namespace Viserio\Component\Console\Tests\Container\Provider;

use Symfony\Component\Console\Application as SymfonyConsole;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Events\Container\Provider\EventsServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class ConsoleServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        $count = 0;

        foreach ($this->containerBuilder->getPipelineConfig()->getBeforeOptimizationPipelines() as $pipeline) {
            if ($pipeline instanceof AddConsoleCommandPipe) {
                $count++;
            }
        }

        self::assertSame(1, $count);

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertInstanceOf(Application::class, $this->container->get(SymfonyConsole::class));
        self::assertInstanceOf(Application::class, $this->container->get('console'));
        self::assertInstanceOf(Application::class, $this->container->get('cerebro'));
        self::assertSame('UNKNOWN', $console->getVersion());
        self::assertSame('UNKNOWN', $console->getName());
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new EventsServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
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
