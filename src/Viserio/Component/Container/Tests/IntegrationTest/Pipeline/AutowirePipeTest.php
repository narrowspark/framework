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
use Psr\Container\ContainerInterface;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Tests\Fixture\Autowire\A;
use Viserio\Component\Container\Tests\Fixture\Autowire\B;
use Viserio\Component\Container\Tests\Fixture\Autowire\BadParentTypeHintedArgument;
use Viserio\Component\Container\Tests\Fixture\Autowire\BadTypeHintedArgument;
use Viserio\Component\Container\Tests\Fixture\Autowire\C;
use Viserio\Component\Container\Tests\Fixture\Autowire\CannotBeAutowired;
use Viserio\Component\Container\Tests\Fixture\Autowire\CollisionA;
use Viserio\Component\Container\Tests\Fixture\Autowire\CollisionB;
use Viserio\Component\Container\Tests\Fixture\Autowire\CollisionInterface;
use Viserio\Component\Container\Tests\Fixture\Autowire\DefaultParameter;
use Viserio\Component\Container\Tests\Fixture\Autowire\DependentOnEmptyClass;
use Viserio\Component\Container\Tests\Fixture\Autowire\DInterface;
use Viserio\Component\Container\Tests\Fixture\Autowire\F;
use Viserio\Component\Container\Tests\Fixture\Autowire\G;
use Viserio\Component\Container\Tests\Fixture\Autowire\H;
use Viserio\Component\Container\Tests\Fixture\Autowire\I;
use Viserio\Component\Container\Tests\Fixture\Autowire\IInterface;
use Viserio\Component\Container\Tests\Fixture\Autowire\J;
use Viserio\Component\Container\Tests\Fixture\Autowire\K;
use Viserio\Component\Container\Tests\Fixture\Autowire\MultipleArgumentsOptionalScalar;
use Viserio\Component\Container\Tests\Fixture\Autowire\MultipleArgumentsOptionalScalarLast;
use Viserio\Component\Container\Tests\Fixture\Autowire\NotFoundParam;
use Viserio\Component\Container\Tests\Fixture\Autowire\NotGuessableArgument;
use Viserio\Component\Container\Tests\Fixture\Autowire\NotGuessableArgumentForSubclass;
use Viserio\Component\Container\Tests\Fixture\Autowire\NotWireable;
use Viserio\Component\Container\Tests\Fixture\Autowire\OptionalParameter;
use Viserio\Component\Container\Tests\Fixture\Autowire\PrivateConstructor;
use Viserio\Component\Container\Tests\Fixture\Autowire\SetterInjection;
use Viserio\Component\Container\Tests\Fixture\Autowire\VariadicClass;
use Viserio\Component\Container\Tests\Fixture\DeprecatedClass;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeCallableClass;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeParameterAndConstructorParameterClass;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeWithConstructorParameterClass;
use Viserio\Contract\Container\Exception\BindingResolutionException;
use Viserio\Contract\Container\Exception\OutOfBoundsException;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Contract\Container\Exception\UnresolvableDependencyException;

/**
 * @internal
 *
 * @small
 */
final class AutowirePipeTest extends TestCase
{
    public function testProcessClosure(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('closure', function (): void {
        });

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ClosureDefinition $definition */
        $definition = $container->getDefinition('closure');

        self::assertCount(0, $definition->getArguments());
    }

    public function testProcessClosureWithArguments(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('closure', function (ContainerInterface $container, string $key = null): void {
        });

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ClosureDefinition $definition */
        $definition = $container->getDefinition('closure');

        self::assertCount(1, $definition->getArguments());
    }

    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->bind(EmptyClass::class);
        $container->bind(DependentOnEmptyClass::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(DependentOnEmptyClass::class);

        self::assertSame(DependentOnEmptyClass::class, $definition->getClass());
        self::assertCount(1, $definition->getArguments());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(EmptyClass::class, $ref->getName());
        self::assertSame(EmptyClass::class, $ref->getType());
    }

    public function testProcessVariadic(): void
    {
        $container = new ContainerBuilder();
        $container->bind(EmptyClass::class);
        $container->singleton(VariadicClass::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(VariadicClass::class);

        self::assertSame(VariadicClass::class, $definition->getClass());
        self::assertCount(1, $definition->getArguments());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(EmptyClass::class, $ref->getName());
    }

    public function testProcessWithAutowireParent(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [c]: argument [$a] of method [Viserio\Component\Container\Tests\Fixture\Autowire\C::__construct] references interface [Viserio\Component\Container\Tests\Fixture\Autowire\AInterface] but no such service exists. You should maybe alias this interface to the existing [Viserio\Component\Container\Tests\Fixture\Autowire\B] service.');

        $container = new ContainerBuilder();
        $container->singleton(B::class);
        $container->singleton('c', C::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('c');

        self::assertCount(1, $definition->getArguments());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(B::class, $ref->getName());
    }

    public function testProcessWithAutowireInterface(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [g]: argument [$d] of method [Viserio\Component\Container\Tests\Fixture\Autowire\G::__construct] references interface [Viserio\Component\Container\Tests\Fixture\Autowire\DInterface] but no such service exists. You should maybe alias this interface to the existing [Viserio\Component\Container\Tests\Fixture\Autowire\F] service.');

        $container = new ContainerBuilder();

        $container->bind(F::class);
        $container->bind('g', G::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('g');

        self::assertCount(3, $definition->getArguments());
        self::assertEquals(F::class, (string) $definition->getArgument(0));
        self::assertEquals(F::class, (string) $definition->getArgument(1));
        self::assertEquals(F::class, (string) $definition->getArgument(2));
    }

    public function testProcessWithCompleteExistingDefinition(): void
    {
        $container = new ContainerBuilder();
        $container->bind(B::class);
        $container->bind(DInterface::class, F::class);
        $container->bind(H::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(H::class);

        self::assertCount(2, $definition->getArguments());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertEquals(B::class, $ref->getName());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(1);

        self::assertEquals(DInterface::class, $ref->getName());
    }

    public function testProcessWithPrivateConstructorThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid service [Viserio\Component\Container\Tests\Fixture\Autowire\PrivateConstructor]: its constructor must be public.');

        $container = new ContainerBuilder();
        $container->singleton(PrivateConstructor::class);

        $this->process($container);
    }

    public function testProcessWithTypeCollision(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\CannotBeAutowired]: argument [$collision] of method [__construct] references interface [Viserio\Component\Container\Tests\Fixture\Autowire\CollisionInterface] but no such service exists. You should maybe alias this interface to one of these existing services: ["Viserio\Component\Container\Tests\Fixture\Autowire\CollisionA", "b", "Viserio\Component\Container\Tests\Fixture\Autowire\CollisionB"].');

        $container = new ContainerBuilder();
        $container->bind(CollisionA::class);
        $container->bind('b', CollisionB::class);
        $container->bind(CollisionB::class);
        $container->bind(CannotBeAutowired::class);

        $this->process($container);
    }

    public function testProcessWithTypeNotGuessable(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\NotGuessableArgument]: argument [$emptyClass] of method [__construct] references class [Viserio\Component\Container\Tests\Fixture\EmptyClass] but no such service exists. You should maybe alias this class to one of these existing services: ["a1", "a2"].');

        $container = new ContainerBuilder();
        $container->bind('a1', EmptyClass::class);
        $container->bind('a2', EmptyClass::class);
        $container->bind(NotGuessableArgument::class);

        $this->process($container);
    }

    public function testProcessWithTypeNotGuessableWithSubclass(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\NotGuessableArgumentForSubclass]: argument [$k] of method [__construct] references class [Viserio\Component\Container\Tests\Fixture\Autowire\A] but no such service exists. You should maybe alias this class to one of these existing services: ["a1", "a2"].');

        $container = new ContainerBuilder();
        $container->bind('a1', B::class);
        $container->bind('a2', B::class);
        $container->bind(NotGuessableArgumentForSubclass::class);

        $this->process($container);
    }

    public function testProcessWithTypeNotGuessableNoServiceFound(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\CannotBeAutowired]: argument [$collision] of method [__construct] references interface [Viserio\Component\Container\Tests\Fixture\Autowire\CollisionInterface] but no such service exists. Did you create a class that implements this interface?');

        $container = new ContainerBuilder();
        $container->bind(CannotBeAutowired::class);

        $this->process($container);
    }

    public function testProcessWithTypeNotGuessableWithTypeSet(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('a1', EmptyClass::class);
        $container->singleton('a2', EmptyClass::class);
        $container->bind(EmptyClass::class);
        $container->bind(NotGuessableArgument::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(NotGuessableArgument::class);

        self::assertSame(NotGuessableArgument::class, $definition->getClass());
        self::assertCount(1, $definition->getArguments());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(EmptyClass::class, $ref->getName());
    }

    public function testProcessWithTypeSet(): void
    {
        $container = new ContainerBuilder();
        $container->singleton('a1', CollisionA::class);
        $container->singleton('a2', CollisionB::class);
        $container->setAlias('a2', CollisionInterface::class);
        $container->bind(CannotBeAutowired::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(CannotBeAutowired::class);

        self::assertSame(CannotBeAutowired::class, $definition->getClass());
        self::assertCount(1, $definition->getArguments());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(CollisionInterface::class, $ref->getType());
        self::assertSame('a2', $ref->getName());
    }

    public function testProcessBindingsAreAutoCreated(): void
    {
        $container = new ContainerBuilder();
        $container->bind(DependentOnEmptyClass::class);

        $this->process($container);

        self::assertTrue($container->hasDefinition(EmptyClass::class));

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(DependentOnEmptyClass::class);

        self::assertSame(DependentOnEmptyClass::class, $definition->getClass());
        self::assertCount(1, $definition->getArguments());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(EmptyClass::class, $ref->getName());
        self::assertSame(EmptyClass::class, $ref->getType());
    }

    public function testProcessWithOptionalParameter(): void
    {
        $container = new ContainerBuilder();
        $container->bind(A::class);
        $container->bind(EmptyClass::class);
        $container->bind(OptionalParameter::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(OptionalParameter::class);

        try {
            $definition->getArgument(0);

            self::fail('No exception was thrown');
        } catch (\OutOfBoundsException $exception) {
            self::assertInstanceOf(OutOfBoundsException::class, $exception);
        }

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(1);

        self::assertSame(A::class, $ref->getName());

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(2);

        self::assertSame(EmptyClass::class, $ref->getName());
    }

    public function testProcessWithDefaultParameter(): void
    {
        $container = new ContainerBuilder();
        $container->bind(DefaultParameter::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(DefaultParameter::class);

        /** @var \Viserio\Component\Container\Definition\ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(EmptyClass::class, $ref->getName());

        try {
            $definition->getArgument(1);

            self::fail('No exception was thrown');
        } catch (\OutOfBoundsException $exception) {
            self::assertInstanceOf(OutOfBoundsException::class, $exception);
        }
    }

    public function testProcessWithClassNotFoundThrowsException(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\BadTypeHintedArgument]: argument [$undefined] of method [__construct] has type [Viserio\Component\Container\Tests\Fixture\Autowire\Undefined] but this class is properly not autoloaded or doesn\'t exist.');

        $container = new ContainerBuilder();
        $container->bind(BadTypeHintedArgument::class);

        $this->process($container);
    }

    public function testProcessWithParentClassNotFoundThrowsException(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\BadParentTypeHintedArgument]: argument [$class] of method [__construct] has type [Viserio\Component\Container\Tests\Fixture\Autowire\OptionalClass] but this class is missing a parent class (Class Viserio\Component\Container\Tests\Fixture\Autowire\NotExistClass not found).');

        $container = new ContainerBuilder();
        $container->bind(EmptyClass::class);
        $container->bind(BadParentTypeHintedArgument::class);

        $this->process($container);
    }

    public function testProcessWithOptionalScalarArgsDontMessUpOrder(): void
    {
        $container = new ContainerBuilder();
        $container->bind('with_optional_scalar', MultipleArgumentsOptionalScalar::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('with_optional_scalar');

        self::assertEquals(
            [
                (new ReferenceDefinition(A::class))->setType(A::class)->setVariableName('a')->setMethodCalls(),
                // use the default value
                'default_val',
                (new ReferenceDefinition(EmptyClass::class))->setType(EmptyClass::class)->setVariableName('lille')->setMethodCalls(),
            ],
            $definition->getArguments()
        );
    }

    public function testProcessWithOptionalScalarArgsNotPassedIfLast(): void
    {
        $container = new ContainerBuilder();
        $container->bind('with_optional_scalar_last', MultipleArgumentsOptionalScalarLast::class);

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('with_optional_scalar_last');

        self::assertEquals(
            [
                (new ReferenceDefinition(A::class))->setType(A::class)->setVariableName('a')->setMethodCalls(),
                (new ReferenceDefinition(EmptyClass::class))->setType(EmptyClass::class)->setVariableName('lille')->setMethodCalls(),
            ],
            $definition->getArguments()
        );
    }

    public function testProcessWithObjectValuesAreIgnoredForAutowire(): void
    {
        $container = new ContainerBuilder();
        $container->bind('foo', new \stdClass());

        $this->process($container);

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition('foo');

        self::assertEquals([], $definition->getArguments());
    }

    public function testProcessInterfaceWithNoImplementationSuggestToWriteOne(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\K]: argument [$i] of method [__construct] references interface [Viserio\Component\Container\Tests\Fixture\Autowire\IInterface] but no such service exists. Did you create a class that implements this interface?');

        $container = new ContainerBuilder();
        $container->bind(K::class);

        $this->process($container);
    }

    /**
     * @group legacy
     * @expectedDeprecation deprecated
     */
    public function testProcessDoesTriggerDeprecations(): void
    {
        $container = new ContainerBuilder();
        $container->bind('deprecated', DeprecatedClass::class)
            ->setDeprecated();
        $container->bind('bar', A::class);
        $container->bind('foo', EmptyClass::class);

        $this->process($container);

        self::assertTrue($container->hasDefinition('deprecated'));
        self::assertTrue($container->hasDefinition('foo'));
        self::assertTrue($container->hasDefinition('bar'));
    }

    public function testProcessWithFactory(): void
    {
        $container = new ContainerBuilder();
        $container->bind('create', FactoryClass::class . '@create');
        $container->bind('static_create', [FactoryClass::class, 'staticCreate']);

        $this->process($container);

        self::assertTrue($container->hasDefinition('create'));
    }

    public function testProcessWithClassParameterAndInvoke(): void
    {
        $container = new ContainerBuilder();
        $container->bind('invoke', InvokeWithConstructorParameterClass::class . '@__invoke');
        $container->bind('invoke2', [InvokeWithConstructorParameterClass::class, '__invoke']);

        $this->process($container);

        self::assertTrue($container->hasDefinition('invoke'));
        self::assertTrue($container->hasDefinition('invoke2'));

        /** @var \Viserio\Component\Container\Definition\FactoryDefinition $definition */
        $definition = $container->getDefinition('invoke');
        /** @var \Viserio\Component\Container\Definition\FactoryDefinition $definition2 */
        $definition2 = $container->getDefinition('invoke2');

        self::assertCount(1, $definition->getClassArguments());
        self::assertCount(1, $definition2->getClassArguments());
        self::assertCount(0, $definition->getArguments());
        self::assertCount(0, $definition2->getArguments());
        self::assertEquals($definition->getClassArguments(), $definition2->getClassArguments());
    }

    public function testProcessWithClassParameterAndInvokeParameter(): void
    {
        $container = new ContainerBuilder();
        $container->bind('invoke', [InvokeParameterAndConstructorParameterClass::class, '__invoke']);

        $this->process($container);

        self::assertTrue($container->hasDefinition('invoke'));

        /** @var \Viserio\Component\Container\Definition\FactoryDefinition $definition */
        $definition = $container->getDefinition('invoke');

        self::assertCount(1, $definition->getClassArguments());
        self::assertCount(1, $definition->getArguments());
        self::assertSame(EmptyClass::class, $definition->getClassArgument(0)->getName());
        self::assertSame(InvokeCallableClass::class, $definition->getArgument(0)->getName());
    }

    public function testProcessWithExceptionWhenAliasExists(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [j]: argument [$i] of method [Viserio\Component\Container\Tests\Fixture\Autowire\J::__construct] references class [Viserio\Component\Container\Tests\Fixture\Autowire\I] but no such service exists. Try changing the type-hint to [Viserio\Component\Container\Tests\Fixture\Autowire\IInterface] instead.');

        $container = new ContainerBuilder();
        // multiple I services... but there *is* IInterface available
        $container->bind('i', I::class);
        $container->bind('i2', I::class);
        $container->setAlias('i', IInterface::class);
        // J type-hints against I concretely
        $container->bind('j', J::class);

        $this->process($container);
    }

    public function testProcessWithExceptionWhenAliasDoesNotExist(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [j]: argument [$i] of method [Viserio\Component\Container\Tests\Fixture\Autowire\J::__construct] references class [Viserio\Component\Container\Tests\Fixture\Autowire\I] but no such service exists. You should maybe alias this class to one of these existing services: ["i", "i2"].');

        $container = new ContainerBuilder();
        // multiple I instances... but no IInterface alias
        $container->bind('i', I::class);
        $container->bind('i2', I::class);
        // J type-hints against I concretely
        $container->bind('j', J::class);

        $this->process($container);
    }

    public function testSetterInjection(): void
    {
        $container = new ContainerBuilder();
        $container->bind(EmptyClass::class);

        $container->bind('setter_injection', SetterInjection::class)
            ->addMethodCall('setWithCallsConfigured', ['manual_arg1', 'manual_arg2'])
            ->addMethodCall('setEmpty');

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition('setter_injection');
        $methodCalls = $definition->getMethodCalls();

        self::assertEquals(
            ['setWithCallsConfigured', 'setEmpty'],
            \array_column($methodCalls, 0)
        );

        // test setWithCallsConfigured args
        self::assertEquals(
            ['manual_arg1', 'manual_arg2'],
            $methodCalls[0][1]
        );

        // test setEmpty args
        self::assertEquals(
            [(new ReferenceDefinition(EmptyClass::class))->setType(EmptyClass::class)->setVariableName('emptyClass')->setMethodCalls()],
            $methodCalls[1][1]
        );
    }

    public function testExplicitMethodInjection(): void
    {
        $container = new ContainerBuilder();
        $container->bind(A::class);
        $container
            ->bind('setter_injection', SetterInjection::class)
            ->addMethodCall('notASetter');

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition('setter_injection');
        $methodCalls = $definition->getMethodCalls();

        self::assertEquals(
            ['notASetter'],
            \array_column($methodCalls, 0)
        );
        self::assertEquals(
            [(new ReferenceDefinition(A::class))->setType(A::class)->setVariableName('a')->setMethodCalls()],
            $methodCalls[0][1]
        );
    }

    /**
     * @dataProvider provideNotWireableCallsCases
     *
     * @param null|string $method
     * @param string      $expectedException
     * @param string      $expectedMsg
     */
    public function testNotWireableCalls(?string $method, string $expectedException, string $expectedMsg): void
    {
        $container = new ContainerBuilder();
        $foo = $container->bind('foo', NotWireable::class)
            ->addMethodCall('setBar')
            ->addMethodCall('setOptionalNotAutowireable')
            ->addMethodCall('setOptionalNoTypeHint')
            ->addMethodCall('setOptionalArgNoAutowireable');

        if ($method !== null) {
            $foo->addMethodCall($method);
        }

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMsg);

        $this->process($container);
    }

    public function provideNotWireableCallsCases(): iterable
    {
        return [
            ['setNotAutowireable', UnresolvableDependencyException::class, 'Cannot autowire service [foo]: argument [$n] of method [Viserio\Component\Container\Tests\Fixture\Autowire\NotWireable::setNotAutowireable] has type [Viserio\Component\Container\Tests\Fixture\Autowire\NotARealClass] but this class is properly not autoloaded or doesn\'t exist..'],
            ['setDifferentNamespace', UnresolvableDependencyException::class, 'Cannot autowire service [foo]: argument [$n] of method [Viserio\Component\Container\Tests\Fixture\Autowire\NotWireable::setDifferentNamespace] has type [Foo\stdClass] but this class is properly not autoloaded or doesn\'t exist..'],
            ['setProtectedMethod', BindingResolutionException::class, 'Method [setProtectedMethod] of class [Viserio\Component\Container\Tests\Fixture\Autowire\NotWireable] must be public.'],
        ];
    }

    public function testSuggestRegisteredServicesWithSimilarCase(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [foo]: argument [$sam] of method [Viserio\Component\Container\Tests\Fixture\Autowire\NotWireable::setNotAutowireableBecauseOfATypo] has type [Viserio\Component\Container\Tests\Fixture\Autowire\lesTilleuls] but this class is properly not autoloaded or doesn\'t exist.');

        $container = new ContainerBuilder();
        $container->bind('foo', NotWireable::class)
            ->addMethodCall('setNotAutowireableBecauseOfATypo');

        $this->process($container);
    }

    public function testProcessNotExisintActionParam(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Cannot autowire service [Viserio\Component\Container\Tests\Fixture\Autowire\NotFoundParam]: argument [$notExisting] of method [__construct] has type [Viserio\Component\Container\Tests\Fixture\Autowire\NotExisting] but this class is properly not autoloaded or doesn\'t exist.');

        $container = new ContainerBuilder();
        $container->singleton(EmptyClass::class);
        $container->singleton(NotFoundParam::class);

        $this->process($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new AutowirePipe();

        $pipe->process($container);
    }
}
