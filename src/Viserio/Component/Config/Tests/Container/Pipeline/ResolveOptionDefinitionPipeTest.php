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

namespace Viserio\Component\Config\Tests\Container\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Component\Config\Container\Pipeline\ResolveOptionDefinitionPipe;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultOptionConfiguration;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\DimensionsOptionDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\Exception\NotFoundException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Config\Container\Pipeline\ResolveOptionDefinitionPipe
 *
 * @small
 */
final class ResolveOptionDefinitionPipeTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->bind(RepositoryContract::class);
        $container->singleton('foo', stdClass::class)
            ->addArgument(new OptionDefinition('params', ConnectionComponentDefaultOptionConfiguration::class));

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');
        /** @var ReferenceDefinition $reference */
        $reference = $definition->getArgument(0);

        self::assertTrue($reference->getChanges()['method_calls']);
        self::assertSame([['get', ['doctrine.connection.params'], false]], $reference->getMethodCalls());
    }

    public function testDimensionProcess(): void
    {
        $container = new ContainerBuilder();
        $container->bind(RepositoryContract::class);
        $container->singleton('foo', stdClass::class)
            ->addArgument(new DimensionsOptionDefinition(ConnectionComponentConfiguration::class));

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');
        /** @var ReferenceDefinition $reference */
        $reference = $definition->getArgument(0);

        self::assertTrue($reference->getChanges()['method_calls']);
        self::assertSame([['get', ['doctrine.connection'], false]], $reference->getMethodCalls());
    }

    public function testProcessThrowException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->process(new ContainerBuilder());
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new ResolveOptionDefinitionPipe();

        $pipe->process($container);
    }
}
