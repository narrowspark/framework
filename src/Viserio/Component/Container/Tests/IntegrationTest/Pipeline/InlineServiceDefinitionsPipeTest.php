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
use stdClass;
use Viserio\Component\Container\Argument\ClosureArgument;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Definition\UndefinedDefinition;
use Viserio\Component\Container\Pipeline\AnalyzeServiceDependenciesPipe;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Pipeline\InlineServiceDefinitionsPipe;
use Viserio\Component\Container\Tests\Fixture\Autowire\VariadicClass;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Contract\Container\Definition\Definition;

/**
 * @internal
 *
 * @small
 */
final class InlineServiceDefinitionsPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $inlineable = $container->bind(EmptyClass::class)
            ->setPublic(false);

        $container->bind('service', VariadicClass::class);

        $this->process($container, true);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('service');

        self::assertInstanceOf(Definition::class, $definition->getArgument(0));
        self::assertEquals($inlineable, $definition->getArgument(0));
        self::assertFalse($container->hasDefinition(EmptyClass::class));
    }

    public function testProcessDoesNotInlinesWhenAliasedServiceIsShared(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo')
            ->setPublic(false);

        $container->setAlias('foo', 'moo');

        $container->singleton('service')
            ->setArguments([$ref = new ReferenceDefinition('foo')]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('service');
        $arguments = $definition->getArguments();

        self::assertSame($ref, $arguments[0]);
    }

    public function testProcessDoesInlineNonSharedService(): void
    {
        $container = new ContainerBuilder();

        $container->bind('foo', stdClass::class)
            ->setPublic(true);

        $bar = $container->bind('bar', stdClass::class);

        $container->setAlias('bar', 'moo');

        $container->singleton('service', stdClass::class)
            ->setArguments([new ReferenceDefinition('foo'), $ref = new ReferenceDefinition('moo'), new ReferenceDefinition('bar')]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('service');
        $arguments = $definition->getArguments();

        self::assertEquals($container->getDefinition('foo'), $arguments[0]);
        self::assertNotSame($container->getDefinition('foo'), $arguments[0]);
        self::assertSame($ref, $arguments[1]);
        self::assertEquals($bar, $arguments[2]);
        self::assertNotSame($bar, $arguments[2]);
        self::assertFalse($container->has('bar'));
    }

    public function testProcessDoesNotInlineMixedServicesLoop(): void
    {
        $container = new ContainerBuilder();
        $container->bind('foo', stdClass::class)
            ->addArgument(new ReferenceDefinition('bar'))
            ->setPublic(true);
        $container->singleton('bar', stdClass::class)
            ->addMethodCall('setFoo', [new ReferenceDefinition('foo')]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');

        self::assertEquals(new ReferenceDefinition('bar'), $definition->getArgument(0));
    }

    public function testProcessThrowsOnNonSharedLoops(): void
    {
        $this->expectException(\Viserio\Contract\Container\Exception\CircularDependencyException::class);
        $this->expectExceptionMessage('Circular reference detected for service [bar]; path: [bar -> foo -> bar].');

        $container = new ContainerBuilder();
        $container->bind('foo')
            ->addArgument(new ReferenceDefinition('bar'))
            ->setPublic(true);
        $container->bind('bar')
            ->addMethodCall('setFoo', [new ReferenceDefinition('foo')])
            ->setPublic(true);

        $this->process($container);
    }

    public function testProcessNestedNonSharedServices(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo', stdClass::class)
            ->addArgument(new ReferenceDefinition('bar1'))
            ->addArgument(new ReferenceDefinition('bar2'))
            ->setPublic(true);
        $container->bind('bar1', stdClass::class)
            ->addArgument(new ReferenceDefinition('baz'))
            ->setPublic(true);
        $container->bind('bar2', stdClass::class)
            ->addArgument(new ReferenceDefinition('baz'))
            ->setPublic(true);
        $container->bind('baz', stdClass::class)
            ->setPublic(true);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');

        $baz1 = $definition->getArgument(0)->getArgument(0);
        $baz2 = $definition->getArgument(1)->getArgument(0);

        $definition2 = $container->getDefinition('baz');

        self::assertEquals($definition2, $baz1);
        self::assertEquals($definition2, $baz2);
        self::assertNotSame($baz1, $baz2);
    }

    public function testProcessInlinesIfMultipleReferencesButAllFromTheSameDefinition(): void
    {
        $container = new ContainerBuilder();

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $a */
        $a = $container->singleton('a', stdClass::class)
            ->setPublic(false);
        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $b */
        $b = $container->singleton('b', stdClass::class)
            ->addArgument(new ReferenceDefinition('a'))
            ->addArgument((new ObjectDefinition('c', stdClass::class, 1))->addArgument(new ReferenceDefinition('a')));

        $this->process($container);

        /** @var ReferenceDefinition[]|\Viserio\Contract\Container\Definition\ObjectDefinition[] $arguments */
        $arguments = $b->getArguments();

        self::assertSame($a->getHash(), $arguments[0]->getHash());

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $def */
        $def = $arguments[1];
        /** @var ReferenceDefinition[]|\Viserio\Contract\Container\Definition\ObjectDefinition[] $inlinedArguments */
        $inlinedArguments = $def->getArguments();

        self::assertSame($a->getHash(), $inlinedArguments[0]->getHash());
    }

    public function testProcessInlinesPrivateFactoryReference(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('a', FactoryClass::class)
            ->setPublic(false);

        $b = $container
            ->singleton('b', [new ReferenceDefinition('a'), 'add'])
            ->setPublic(false);

        $container->singleton('foo', stdClass::class)
            ->setArguments([$ref = new ReferenceDefinition('b')]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');

        /** @var ReferenceDefinition[]|\Viserio\Contract\Container\Definition\ObjectDefinition[] $inlinedArguments */
        $inlinedArguments = $definition->getArguments();

        self::assertSame($b->getHash(), $inlinedArguments[0]->getHash());
    }

    public function testProcessDoesNotInlinePrivateFactoryIfReferencedMultipleTimesWithinTheSameDefinition(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('a', FactoryClass::class)
            ->setPublic(true);
        $container
            ->singleton('b', [new ReferenceDefinition('a'), 'add'])
            ->setPublic(false);

        $container
            ->singleton('foo')
            ->setArguments([
                $ref1 = new ReferenceDefinition('b'),
                $ref2 = new ReferenceDefinition('b'),
            ]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');

        $args = $definition->getArguments();

        self::assertSame($ref1, $args[0]);
        self::assertSame($ref2, $args[1]);
    }

    public function testProcessDoesNotInlineReferenceWhenUsedByInlineFactory(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('a', FactoryClass::class)
            ->setPublic(true);
        $container
            ->singleton('b', [new ReferenceDefinition('a'), 'getInstance'])
            ->setPublic(false);

        $container->singleton('foo')
            ->setArguments([
                $ref = new ReferenceDefinition('b'),
                new FactoryDefinition('c', [new ReferenceDefinition('b'), 'getInstance'], 1),
            ])
            ->setPublic(true);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');
        $args = $definition->getArguments();

        self::assertSame($ref, $args[0]);
    }

    public function testProcessDoesNotInlineWhenServiceIsPrivateButLazy(): void
    {
        $container = new ContainerBuilder();

        $container->singleton('foo')
            ->setPublic(false)
            ->setLazy(true);

        $container->singleton('service')
            ->setArguments([$ref = new ReferenceDefinition('foo')]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('service');
        $arguments = $definition->getArguments();

        self::assertSame($ref, $arguments[0]);
    }

    public function testProcessDoesNotInlineWhenServiceReferencesItself(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('foo')
            ->setPublic(false)
            ->addMethodCall('foo', [$ref = new ReferenceDefinition('foo')]);

        $this->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');
        $calls = $definition->getMethodCalls();

        self::assertSame($ref, $calls[0][1][0]);
    }

    public function testProcessDoesNotSetLazyArgumentValuesAfterInlining(): void
    {
        $container = new ContainerBuilder();
        $container->bind('inline');
        $container->singleton('service-closure')
            ->setArguments([new ClosureArgument(new ReferenceDefinition('inline'))]);
        $container->singleton('iterator')
            ->setArguments([new IteratorArgument([new ReferenceDefinition('inline')])]);

        $this->process($container);

        /** @var UndefinedDefinition $definition */
        $definition = $container->getDefinition('service-closure');

        $values = $definition->getArgument(0)->getValue();

        self::assertInstanceOf(ReferenceDefinition::class, $values[0]);
        self::assertSame('inline', $values[0]->getName());

        /** @var UndefinedDefinition $definition2 */
        $definition2 = $container->getDefinition('iterator');
        $values = $definition2->getArgument(0)->getValue();

        self::assertInstanceOf(ReferenceDefinition::class, $values[0]);
        self::assertSame('inline', $values[0]->getName());
    }

    /**
     * @param ContainerBuilder $container
     * @param bool             $autowire
     */
    private function process(ContainerBuilder $container, bool $autowire = false): void
    {
        $pipes = [];

        if ($autowire) {
            $pipes[] = new AutowirePipe();
        }

        $pipes[] = new InlineServiceDefinitionsPipe(new AnalyzeServiceDependenciesPipe());

        foreach ($pipes as $pipe) {
            $pipe->process($container);
        }
    }
}
