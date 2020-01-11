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

namespace Viserio\Component\Container\Tests\IntegrationTest\Pipeline;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe;
use Viserio\Component\Container\Tests\Fixture\Processor\BadProcessor;
use Viserio\Component\Container\Tests\Fixture\Processor\FooParameterProcessor;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class RegisterParameterProcessorsPipeTest extends TestCase
{
    public function testSimpleProcessor(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo', FooParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);

        $this->process($container);

        self::assertTrue($container->hasDefinition('container.parameter.processors'));
        self::assertTrue($container->hasParameter('container.parameter.provided.processor.types'));
        self::assertSame(['foo' => ['string']], $container->getParameter('container.parameter.provided.processor.types')->getValue());
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
