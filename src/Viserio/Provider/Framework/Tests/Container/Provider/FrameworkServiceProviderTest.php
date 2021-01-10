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

namespace Viserio\Provider\Framework\Tests\Container\Provider;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe;
use Viserio\Component\Container\Processor\EnvParameterProcessor;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Contract\Container\Processor\ParameterProcessor as ParameterProcessorContract;
use Viserio\Provider\Framework\Container\Provider\FrameworkServiceProvider;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class FrameworkServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(ParameterProcessorContract::class, $this->container->get(EnvParameterProcessor::class));

        /** @var \Viserio\Component\Container\RewindableGenerator $processors */
        $processors = $this->container->get(RegisterParameterProcessorsPipe::RUNTIME_PROCESSORS_KEY);

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
