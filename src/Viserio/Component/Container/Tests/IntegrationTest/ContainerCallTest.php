<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\CallMethodTestClass;
use Viserio\Component\Container\Tests\Fixture\InvokeCallableTestClass;

/**
 * @internal
 */
final class ContainerCallTest extends BaseContainerTest
{
    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testNoParameters(ContainerBuilder $builder): void
    {
        $result = $builder->build()->call(function () {
            return 42;
        });

        static::assertEquals(42, $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testParametersOrdered(ContainerBuilder $builder): void
    {
        $result = $builder->build()->call(function ($foo, $bar) {
            return $foo . $bar;
        }, ['foo', 'bar']);

        static::assertEquals('foobar', $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testParametersIndexedByName(ContainerBuilder $builder): void
    {
        $result = $builder->build()->call(function ($foo, $bar) {
            return $foo . $bar;
        }, [
            // Reverse order: should still work
            'bar' => 'buzz',
            'foo' => 'fizz',
        ]);

        static::assertEquals('fizzbuzz', $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testParameterWithDefinitionsIndexed(ContainerBuilder $builder): void
    {
        $builder->instance('bar', 'bam');

        $container = $builder->build();

        $self   = $this;
        $result = $builder->build()->call(function ($foo, $bar) use ($self) {
            $self->assertInstanceOf('stdClass', $bar);

            return $foo;
        }, [
            'bar' => $container->make('stdClass'),
            'foo' => $container->get('bar'),
        ]);

        static::assertEquals('bam', $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testParameterWithDefinitionsNotIndexed(ContainerBuilder $builder): void
    {
        $builder->instance('bar', 'bam');

        $container = $builder->build();

        $self   = $this;
        $result = $container->call(function ($foo, $bar) use ($self) {
            $self->assertInstanceOf('stdClass', $bar);

            return $foo;
        }, [$container->get('bar'), $container->make('stdClass')]);

        static::assertEquals('bam', $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testParameterDefaultValue(ContainerBuilder $builder): void
    {
        $result = $builder->build()->call(function ($foo = 'hello') {
            return $foo;
        });

        static::assertEquals('hello', $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testParameterExplicitValueOverridesDefaultValue(ContainerBuilder $builder): void
    {
        $result    = $builder->build()->call(function ($foo = 'hello') {
            return $foo;
        }, [
            'foo' => 'test',
        ]);

        static::assertEquals('test', $result);

        $result = $builder->build()->call(function ($foo = 'hello') {
            return $foo;
        }, ['test']);

        static::assertEquals('test', $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testParameterFromTypeHint(ContainerBuilder $builder): void
    {
        $value = new \stdClass();

        $builder->instance('stdClass', $value);

        $result = $builder->build()->call(function (\stdClass $foo) {
            return $foo;
        });

        static::assertEquals($value, $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCallsObjectMethods(ContainerBuilder $builder): void
    {
        $object = new CallMethodTestClass();
        $result = $builder->build()->call([$object, 'foo']);

        static::assertEquals(42, $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCreatesAndCallsClassMethodsUsingContainer(ContainerBuilder $builder): void
    {
        $builder->instance(CallMethodTestClass::class, new CallMethodTestClass());

        $result = $builder->build()->call(CallMethodTestClass::class . '@foo');

        static::assertEquals(42, $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCallsStaticMethods(ContainerBuilder $builder): void
    {
        $class  = CallMethodTestClass::class;
        $result = $builder->build()->call([$class, 'bar']);

        static::assertEquals(24, $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCallsInvokableObject(ContainerBuilder $builder): void
    {
        $class  = InvokeCallableTestClass::class;
        $result = $builder->build()->call(new $class());

        static::assertEquals(42, $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCreatesAndCallsInvokableObjectsUsingContainer(ContainerBuilder $builder): void
    {
        $class = InvokeCallableTestClass::class;

        $builder->instance($class, new $class());

        $result = $builder->build()->call($class);

        static::assertEquals(42, $result);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testCallsFunctions(ContainerBuilder $builder): void
    {
        $result = $builder->build()->call('callFunctionTestFunction', [
            'str' => 'foo',
        ]);

        static::assertEquals(3, $result);
    }

    /**
     * @dataProvider provideContainer
     *
     * @param ContainerBuilder $builder
     */
    public function testNotEnoughParameters(ContainerBuilder $builder): void
    {
        $this->expectException(\Invoker\Exception\NotEnoughParametersException::class);
        $this->expectExceptionMessage('Unable to invoke the callable because no value was given for parameter 1 ($foo)');

        $builder->build()->call(function ($foo): void {
        });
    }

    /**
     * @dataProvider provideContainer
     *
     * @param ContainerBuilder $builder
     */
    public function testNotCallable(ContainerBuilder $builder): void
    {
        $this->expectException(\Invoker\Exception\NotCallableException::class);
        $this->expectExceptionMessage('\'foo\' is neither a callable nor a valid container entry');

        $builder->build()->call('foo');
    }
}
