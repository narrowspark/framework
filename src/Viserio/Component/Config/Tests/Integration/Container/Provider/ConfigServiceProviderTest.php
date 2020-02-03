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

namespace Viserio\Component\Config\Tests\Integration\Provider;

use Viserio\Component\Config\Command\ConfigDumpCommand;
use Viserio\Component\Config\Command\ConfigReaderCommand;
use Viserio\Component\Config\Container\Pipeline\ResolveConfigDefinitionPipe;
use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;

/**
 * @internal
 *
 * @covers \Viserio\Component\Console\Container\Provider\ConsoleServiceProvider
 *
 * @small
 */
final class ConfigServiceProviderTest extends AbstractContainerTestCase
{
    public function testGetServices(): void
    {
        $count = 0;

        foreach ($this->containerBuilder->getPipelineConfig()->getBeforeOptimizationPipelines() as $pipeline) {
            if ($pipeline instanceof ResolveConfigDefinitionPipe) {
                $count++;
            }
        }

        self::assertSame(1, $count);

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(ConfigDumpCommand::getDefaultName()));
        self::assertTrue($console->has(ConfigReaderCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('viserio', [
            'console' => [
                'name' => 'test',
                'version' => '1',
            ],
        ]);

        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new ConfigServiceProvider());
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
