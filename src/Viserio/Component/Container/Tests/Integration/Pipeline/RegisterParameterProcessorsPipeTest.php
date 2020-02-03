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

namespace Viserio\Component\Container\Tests\Integration\Pipeline;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe;
use Viserio\Component\Container\Processor\EnvParameterProcessor;
use Viserio\Component\Container\Tests\Fixture\Processor\BadProcessor;
use Viserio\Component\Container\Tests\Fixture\Processor\FooParameterProcessor;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe
 *
 * @small
 */
final class RegisterParameterProcessorsPipeTest extends TestCase
{
    public function testProcessorNonRuntimeProcessors(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo', FooParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);

        $this->process($container);

        self::assertTrue($container->hasDefinition(RegisterParameterProcessorsPipe::PROCESSORS_KEY));
        self::assertTrue($container->hasParameter(RegisterParameterProcessorsPipe::PROCESSOR_TYPES_PARAMETER_KEY));
        self::assertSame(['foo' => ['string']], $container->getParameter(RegisterParameterProcessorsPipe::PROCESSOR_TYPES_PARAMETER_KEY)->getValue());
    }

    public function testProcessorWithRuntimeProcessors(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo', EnvParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);

        $this->process($container);

        self::assertTrue($container->hasDefinition(RegisterParameterProcessorsPipe::RUNTIME_PROCESSORS_KEY));
        self::assertTrue($container->hasParameter(RegisterParameterProcessorsPipe::RUNTIME_PROCESSOR_TYPES_PARAMETER_KEY));
        self::assertSame(['env' => ['bool', 'int', 'float', 'string', 'array']], $container->getParameter(RegisterParameterProcessorsPipe::RUNTIME_PROCESSOR_TYPES_PARAMETER_KEY)->getValue());
    }

    public function testNoProcessor(): void
    {
        $container = new ContainerBuilder();

        $this->process($container);

        self::assertFalse($container->has(RegisterParameterProcessorsPipe::TAG));
    }

    public function testBadProcessor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type [foo] returned by [Viserio\Component\Container\Tests\Fixture\Processor\BadProcessor::getProvidedTypes()], expected one of [array", "bool", "float", "int", "string].');

        $container = new ContainerBuilder();
        $container->singleton('foo', BadProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);

        $this->process($container);
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     */
    private function process(ContainerBuilderContract $container): void
    {
        $pipe = new RegisterParameterProcessorsPipe();

        $pipe->process($container);
    }
}
