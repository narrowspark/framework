<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\{
    ContainerTestCallStub,
    ContainerTestCallStub as ContainerTestCallStubFixture,
    ContainerObjectWithProperties,
    ContainerConcreteFixture,
    ContainerContractFixtureInterface,
    ContainerDefaultValueFixture,
    ContainerDependentFixture,
    ContainerImplementationFixture,
    ContainerLazyExtendFixture,
    ContainerMixedPrimitiveFixture,
    ContainerNestedDependentFixture,
    ContainerPrivateConstructor,
    ContainerTestContextInjectOneFixture,
    FactoryClass,
    FactoryClass as FactoryClassAlias
};
use Viserio\Component\Contract\Container\Exception\BindingResolutionException;

/**
 * @internal
 */
final class ContainerMakeTest extends BaseContainerTest
{
    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testMakeMethod(ContainerBuilder $builder): void
    {
        $this->assertEquals(
            'Hello',
            $builder->build()->make(FactoryClass::class . '@create')
        );
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testClosureResolution(ContainerBuilder $builder): void
    {
        $builder->bind('name', function () {
            return 'Narrowspark';
        });

        $this->assertEquals(
            'Narrowspark',
            $builder->build()->make('name')
        );
    }

//    /**
//     * @param \Viserio\Component\Container\ContainerBuilder $builder
//     *
//     * @dataProvider provideContainer
//     *
//     * @return void
//     */
//    public function testClosureResolutionWithUse(ContainerBuilder $builder): void
//    {
//        $class = new stdClass();
//
//        $builder->singleton('class', function () use ($class) {
//            return $class;
//        });
//
//        $this->assertSame(
//            $class,
//            $builder->build()->make('class')
//        );
//    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testClosureResolutionWithClass(ContainerBuilder $builder): void
    {
        $builder->singleton('class', function () {
            return new FactoryClass();
        });

        $builder->singleton('std', function () {
            return new stdClass();
        });

        $builder->singleton('alias', function () {
            return new FactoryClassAlias();
        });

        $builder->singleton('fixture', function () {
            return new FactoryClass();
        });

        $builder->singleton('group', function () {
            return new ContainerTestCallStub();
        });

        $builder->singleton('group-alias', function () {
            return new ContainerTestCallStubFixture();
        });

        $container = $builder->build();

        $this->assertEquals(
            new FactoryClass(),
            $container->make('class')
        );

        $this->assertEquals(
            new stdClass(),
            $container->make('std')
        );

        $this->assertEquals(
            new FactoryClassAlias(),
            $container->make('alias')
        );

        $this->assertEquals(
            new FactoryClass(),
            $container->make('fixture')
        );

        $this->assertEquals(
            new ContainerTestCallStub(),
            $container->make('group')
        );

        $this->assertEquals(
            new ContainerTestCallStub(),
            $container->make('group-alias')
        );
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testMakeCanResolveCallback(ContainerBuilder $builder): void
    {
        $value = $builder->build()->make(
            function ($container, $test) {
                return $test;
            },
            ['test' => 'test']
        );

        $this->assertSame('test', $value);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testAutoConcreteResolution(ContainerBuilder $builder): void
    {
        $this->assertInstanceOf(
            ContainerConcreteFixture::class,
            $builder->build()->make(ContainerConcreteFixture::class)
        );
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testSharedConcreteResolution(ContainerBuilder $builder): void
    {
        $builder->singleton(ContainerConcreteFixture::class);

        $container = $builder->build();

        $var1 = $container->make(ContainerConcreteFixture::class);
        $var2 = $container->make(ContainerConcreteFixture::class);

        $this->assertSame($var1, $var2);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testParametersCanOverrideDependencies(ContainerBuilder $builder): void
    {
        $mock      = \Mockery::mock(ContainerContractFixtureInterface::class);
        $stub      = new ContainerDependentFixture($mock);
        $resolved  = $builder->build()->make(ContainerNestedDependentFixture::class, [$stub]);

        $this->assertInstanceOf(ContainerNestedDependentFixture::class, $resolved);
        $this->assertEquals($mock, $resolved->inner->impl);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testAbstractToConcreteResolution(ContainerBuilder $builder): void
    {
        $builder->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $class = $builder->build()->make(ContainerDependentFixture::class);

        $this->assertInstanceOf(ContainerImplementationFixture::class, $class->impl);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testNestedDependencyResolution(ContainerBuilder $builder): void
    {
        $builder->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $class = $builder->build()->make(ContainerNestedDependentFixture::class);

        $this->assertInstanceOf(ContainerDependentFixture::class, $class->inner);
        $this->assertInstanceOf(ContainerImplementationFixture::class, $class->inner->impl);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testContainerIsPassedToResolvers(ContainerBuilder $builder): void
    {
        $builder->bind('something', function ($c) {
            return $c;
        });

        $container = $builder->build();

        $this->assertSame($container->make('something'), $container);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testArrayAccess(ContainerBuilder $builder): void
    {
        $builder['something'] = function () {
            return 'foo';
        };

        $this->assertTrue(isset($builder['something']));
        $this->assertEquals('foo', $builder['something']);

        unset($builder['something']);

        $this->assertFalse(isset($builder['something']));

        $builder['foo'] = 'foo';
        $container              = $builder->build();

        $result = $container->make('foo');

        $this->assertSame($result, $container->make('foo'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testAliases(ContainerBuilder $builder): void
    {
        $builder['foo'] = 'bar';
        $builder->setAlias('foo', 'baz');
        $builder->setAlias('baz', 'bat');

        $container = $builder->build();

        $this->assertSame('bar', $container->make('foo'));
        $this->assertSame('bar', $container->make('baz'));
        $this->assertSame('bar', $container->make('bat'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendedBindings(ContainerBuilder $builder): void
    {
        $builder['foo'] = 'foo';
        $builder->extend('foo', function ($container, $old) {
            return $old . 'bar';
        });

        $this->assertSame('foobar', $builder->build()->make('foo'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendedBindingsWithContainerMagicMethod(ContainerBuilder $builder): void
    {
        $builder['foo'] = function () {
            return (object) ['name' => 'narrowspark'];
        };

        $builder->extend('foo', function ($container, $old) {
            $old->oldName = 'viserio';

            return $old;
        });

        $result = $builder->build()->make('foo');

        $this->assertSame('narrowspark', $result->name);
        $this->assertSame('viserio', $result->oldName);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testMultipleExtends(ContainerBuilder $builder): void
    {
        $builder['foo'] = 'foo';

        $builder->extend('foo', function ($container, $old) {
            return $old . 'bar';
        });

        $builder->extend('foo', function ($container, $old) {
            return $old . 'baz';
        });

        $this->assertEquals('foobarbaz', $builder->build()->make('foo'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendInstancesArePreserved(ContainerBuilder $builder): void
    {
        $builder->bind('foo', function () {
            $obj = new stdClass();
            $obj->foo = 'bar';

            return $obj;
        });

        $obj      = new stdClass();
        $obj->foo = 'foo';

        $builder->instance('foo', $obj);

        $builder->extend('foo', function ($container, $obj) {
            $obj->bar = 'baz';

            return $obj;
        });
        $builder->extend('foo', function ($container, $obj) {
            $obj->baz = 'foo';

            return $obj;
        });

        $container = $builder->build();

        $this->assertEquals('foo', $container->make('foo')->foo);
        $this->assertEquals('baz', $container->make('foo')->bar);
        $this->assertEquals('foo', $container->make('foo')->baz);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testInstanceWithProperties(ContainerBuilder $builder): void
    {
        $obj      = new ContainerObjectWithProperties();
        $obj->foo = 'foo';

        $builder->instance('properties', $obj);

        $container = $builder->build();

        $this->assertEquals('foo', $container->make('properties')->foo);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendCanBeCalledBeforeBind(ContainerBuilder $builder): void
    {
        $builder->extend('foo', function ($container, $old) {
            return $old . 'bar';
        });
        $builder['foo'] = 'foo';

        $this->assertEquals('foobar', $builder->build()->make('foo'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testParametersCanBePassedThroughToClosure(ContainerBuilder $builder): void
    {
        $builder->bind('foo', function ($container, $a, $b, $c) {
            return [$a, $b, $c];
        });

        $this->assertEquals([1, 2, 3], $builder->build()->make('foo', [1, 2, 3]));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testResolutionOfDefaultParameters(ContainerBuilder $builder): void
    {
        $instance = $builder->build()->make(ContainerDefaultValueFixture::class);

        $this->assertInstanceOf(ContainerConcreteFixture::class, $instance->stub);
        $this->assertEquals('narrowspark', $instance->default);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testUnableToReflectClass(ContainerBuilder $builder): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('The class [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerPrivateConstructor] is not instantiable.');

        $builder->build()->make(ContainerPrivateConstructor::class);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testBindingResolutionExceptionMessage(ContainerBuilder $builder): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\Fixture\\ContainerContractFixtureInterface] is not resolvable.');

        $builder->build()->make(ContainerContractFixtureInterface::class);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testBindingResolutionExceptionMessageIncludesBuildStack(ContainerBuilder $builder): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\Fixture\\ContainerContractFixtureInterface] is not resolvable. Build stack: [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerTestContextInjectOneFixture].');

        $builder->build()->make(ContainerTestContextInjectOneFixture::class);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testInternalClassWithDefaultParameters(ContainerBuilder $builder): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Unresolvable parameter resolving [$Parameter #0 [ <required> $first ]] in [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerMixedPrimitiveFixture] has no value defined or is not guessable.');

        $builder->build()->make(ContainerMixedPrimitiveFixture::class);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendedBindingsKeepsTypes(ContainerBuilder $builder): void
    {
        $builder->singleton('foo', function () {
            return (object) ['name' => 'narrowspark'];
        });

        $builder->extend('foo', function ($container, $old) {
            $old->oldName = 'viserio';

            return $old;
        });

        $builder->bind('bar', function () {
            return (object) ['name' => 'narrowspark'];
        });

        $builder->extend('bar', function ($container, $old) {
            $old->oldName = 'viserio';

            return $old;
        });

        $container = $builder->build();

        $this->assertSame($container->make('foo'), $container->make('foo'));
        $this->assertNotSame($container->make('bar'), $container->make('bar'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendIsLazyInitialized(ContainerBuilder $builder): void
    {
        ContainerLazyExtendFixture::$initialized = false;

        $builder->bind(ContainerLazyExtendFixture::class);
        $builder->extend(ContainerLazyExtendFixture::class, function ($container, $obj) {
            $obj->init();

            return $obj;
        });

        $this->assertFalse(ContainerLazyExtendFixture::$initialized);

        $builder->build()->make(ContainerLazyExtendFixture::class);

        $this->assertTrue(ContainerLazyExtendFixture::$initialized);
    }
}
