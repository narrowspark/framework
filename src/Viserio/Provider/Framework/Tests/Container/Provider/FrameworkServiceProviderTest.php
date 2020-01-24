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

namespace Viserio\Provider\Framework\Tests\Container\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Contract\Container\Processor\ParameterProcessor as ParameterProcessorContract;
use Viserio\Provider\Framework\Container\Provider\FrameworkServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class FrameworkServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(ParameterProcessorContract::class, $this->container->get(EnvParameterProcessor::class));

        /** @var \Viserio\Component\Container\RewindableGenerator $processors */
        $processors = $this->container->get('viserio.container.parameter.processors');

        self::assertInstanceOf(ParameterProcessorContract::class, $processors->getIterator()->current());
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new FrameworkServiceProvider());
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
