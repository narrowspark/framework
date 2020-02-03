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
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Pipeline\ResolveReferenceAliasesToDependencyReferencesPipe;
use Viserio\Component\Container\Tests\Fixture\Pipeline\AliasA;
use Viserio\Component\Container\Tests\Fixture\Pipeline\ClassA;
use Viserio\Component\Container\Tests\Fixture\Pipeline\ClassB;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\AutowirePipe
 * @covers \Viserio\Component\Container\Pipeline\ResolveReferenceAliasesToDependencyReferencesPipe
 *
 * @small
 */
final class ResolveReferenceAliasesToDependencyReferencesPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $container->bind(ClassA::class);
        $container->setAlias(ClassA::class, AliasA::class);
        $container->bind(ClassB::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(ClassB::class);

        /** @var \Viserio\Contract\Container\Definition\ReferenceDefinition[] $parameters */
        $parameters = $definition->getArguments();

        self::assertSame(ClassA::class, $parameters[0]->getName());
    }

    public function testProcessWithFactory(): void
    {
        $container = new ContainerBuilder();

        $container->bind(ClassA::class);
        $container->setAlias(ClassA::class, AliasA::class);
        $container->bind('setA', ClassB::class . '@setA');

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\FactoryDefinition $definition */
        $definition = $container->getDefinition('setA');

        /** @var \Viserio\Contract\Container\Definition\ReferenceDefinition[] $parameters */
        $parameters = $definition->getArguments();

        self::assertSame(ClassA::class, $parameters[0]->getName());
    }

    /**
     * @group legacy
     * @expectedDeprecation The [Viserio\Component\Container\Tests\Fixture\Pipeline\AliasA] service alias is deprecated. You should stop using it, as it will be removed in the future.
     */
    public function testDeprecationNoticeWhenReferencedByDefinition(): void
    {
        $container = new ContainerBuilder();

        $container->bind(ClassA::class);
        $container->setAlias(ClassA::class, AliasA::class)
            ->setDeprecated();
        $container->bind(ClassB::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(ClassB::class);

        /** @var \Viserio\Contract\Container\Definition\ReferenceDefinition[] $parameters */
        $parameters = $definition->getArguments();

        self::assertSame(ClassA::class, $parameters[0]->getName());
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     */
    private function process(ContainerBuilderContract $container): void
    {
        $pipes = [
            new AutowirePipe(),
            new ResolveReferenceAliasesToDependencyReferencesPipe(),
        ];

        foreach ($pipes as $pipe) {
            $pipe->process($container);
        }
    }
}
