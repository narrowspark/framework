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

namespace Viserio\Component\OptionsResolver\Tests\Provider;

use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\OptionsResolver\Command\OptionReaderCommand;
use Viserio\Component\OptionsResolver\Container\Pipeline\ResolveOptionDefinitionPipe;
use Viserio\Component\OptionsResolver\Container\Provider\OptionsResolverServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class OptionsResolverServiceProviderTest extends AbstractContainerTestCase
{
    public function testGetServices(): void
    {
        $count = 0;

        foreach ($this->containerBuilder->getPipelineConfig()->getBeforeOptimizationPipelines() as $pipeline) {
            if ($pipeline instanceof ResolveOptionDefinitionPipe) {
                $count++;
            }
        }

        self::assertSame(1, $count);

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(OptionDumpCommand::getDefaultName()));
        self::assertTrue($console->has(OptionReaderCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', []);
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new OptionsResolverServiceProvider());
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
