<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use DI\Container as DIContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Component\Container\Container;
use Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubA;
use Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubD;
use Viserio\Component\Container\Tests\Fixture\ContainerConcreteFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerContractFixtureInterface;
use Viserio\Component\Container\Tests\Fixture\ContainerDefaultValueFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerDependentFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerImplementationFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerImplementationTwoFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerInjectVariableFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerLazyExtendFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerMixedPrimitiveFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerNestedDependentFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerPrivateConstructor;
use Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectInstantiationsFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectOneFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectTwoFixture;
use Viserio\Component\Container\Tests\Fixture\ContainerTestNoConstructor;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\Fixture\ServiceFixture;
use Viserio\Component\Container\Tests\Fixture\SimpleFixtureServiceProvider;
use Viserio\Component\Container\Tests\Fixture\SimpleTaggedServiceProvider;
use Viserio\Component\Contract\Container\Exception\BindingResolutionException;
use Viserio\Component\Contract\Container\Exception\NotFoundException;

class ContainerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Container\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $services = [];

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->services  = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];

        foreach (\array_keys($this->services) as $id) {
            $service     = new stdClass();
            $service->id = $id;

            $this->services[$id] = $service;
            $this->container->bind($id, $service);
        }
    }

    public function testClosureResolution(): void
    {
        $this->container->bind('name', function () {
            return 'Narrowspark';
        });

        self::assertEquals('Narrowspark', $this->container->resolve('name'));
    }

    public function testBindIfDoesntRegisterIfServiceAlreadyRegistered(): void
    {
        $this->container->bind('name', function () {
            return 'Narrowspark';
        });

        $this->container->bindIf('name', function () {
            return 'Viserio';
        });

        self::assertEquals('Narrowspark', $this->container->resolve('name'));
    }

    public function testSharedClosureResolution(): void
    {
        $class = new stdClass();

        $this->container->singleton('class', function () use ($class) {
            return $class;
        });

        self::assertSame($class, $this->container->resolve('class'));
    }

    public function testResolveCanResolveCallback(): void
    {
        $value = $this->container->resolve(
            function ($test) {
                return $test;
            },
            ['test' => 'test']
        );

        self::assertSame('test', $value);
    }

    public function testAutoConcreteResolution(): void
    {
        self::assertInstanceOf(ContainerConcreteFixture::class, $this->container->resolve(ContainerConcreteFixture::class));
    }

    public function testResolveMethod(): void
    {
        self::assertEquals('Hello', $this->container->resolve(FactoryClass::class . '::create'));
    }

    public function testResolveMethodFromString(): void
    {
        self::assertEquals('Hello', $this->container->resolveMethod(FactoryClass::class . '::staticCreate'));
        self::assertEquals('Hello', $this->container->resolveMethod(FactoryClass::class . '::staticCreateWitArg', ['name' => 'Hello']));
    }

    public function testSharedConcreteResolution(): void
    {
        $this->container->singleton(ContainerConcreteFixture::class);

        $var1 = $this->container->resolve(ContainerConcreteFixture::class);
        $var2 = $this->container->resolve(ContainerConcreteFixture::class);

        self::assertSame($var1, $var2);
    }

    public function testParametersCanOverrideDependencies(): void
    {
        $mock      = $this->mock(ContainerContractFixtureInterface::class);
        $stub      = new ContainerDependentFixture($mock);
        $resolved  = $this->container->resolve(ContainerNestedDependentFixture::class, [$stub]);

        self::assertInstanceOf(ContainerNestedDependentFixture::class, $resolved);
        self::assertEquals($mock, $resolved->inner->impl);
    }

    public function testResolveNonBoundWithClosure(): void
    {
        $class = $this->container->resolveNonBound(function ($container) {
            return $container;
        });

        self::assertInstanceOf(Container::class, $class);
    }

    public function testAbstractToConcreteResolution(): void
    {
        $this->container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $class = $this->container->resolve(ContainerDependentFixture::class);

        self::assertInstanceOf(ContainerImplementationFixture::class, $class->impl);
    }

    public function testNestedDependencyResolution(): void
    {
        $this->container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $class = $this->container->resolve(ContainerNestedDependentFixture::class);

        self::assertInstanceOf(ContainerDependentFixture::class, $class->inner);
        self::assertInstanceOf(ContainerImplementationFixture::class, $class->inner->impl);
    }

    public function testContainerIsPassedToResolvers(): void
    {
        $this->container->bind('something', function ($c) {
            return $c;
        });

        $c = $this->container->resolve('something');

        self::assertSame($c, $this->container);
    }

    public function testArrayAccess(): void
    {
        $this->container['something'] = function () {
            return 'foo';
        };

        self::assertTrue(isset($this->container['something']));
        self::assertEquals('foo', $this->container['something']);

        unset($this->container['something']);

        self::assertFalse(isset($this->container['something']));

        $this->container['foo'] = 'foo';
        $result                 = $this->container->resolve('foo');

        self::assertSame($result, $this->container->resolve('foo'));
    }

    public function testAliases(): void
    {
        $this->container['foo'] = 'bar';
        $this->container->alias('foo', 'baz');
        $this->container->alias('baz', 'bat');

        self::assertSame('bar', $this->container->resolve('foo'));
        self::assertSame('bar', $this->container->resolve('baz'));
        self::assertSame('bar', $this->container->resolve('bat'));
    }

    public function testBindingsCanBeOverridden(): void
    {
        $this->container['foo'] = 'bar';
        $foo                    = $this->container['foo'];

        self::assertSame('bar', $foo);

        $this->container['foo'] = 'baz';

        self::assertSame('baz', $this->container['foo']);
    }

    public function testExtendedBindings(): void
    {
        $this->container['foo'] = 'foo';
        $this->container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        self::assertSame('foobar', $this->container->resolve('foo'));

        $this->container['foo'] = function () {
            return (object) ['name' => 'narrowspark'];
        };

        $this->container->extend('foo', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $result = $this->container->resolve('foo');

        self::assertSame('narrowspark', $result->name);
        self::assertSame('viserio', $result->oldName);
    }

    public function testMultipleExtends(): void
    {
        $this->container['foo'] = 'foo';

        $this->container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        $this->container->extend('foo', function ($old, $container) {
            return $old . 'baz';
        });

        self::assertEquals('foobarbaz', $this->container->resolve('foo'));
    }

    public function testExtendInstancesArePreserved(): void
    {
        $this->container->bind('foo', function () {
            $obj = new stdClass();
            $obj->foo = 'bar';

            return $obj;
        });

        $obj      = new stdClass();
        $obj->foo = 'foo';

        $this->container->instance('foo', $obj);
        $this->container->extend('foo', function ($obj, $container) {
            $obj->bar = 'baz';

            return $obj;
        });
        $this->container->extend('foo', function ($obj, $container) {
            $obj->baz = 'foo';

            return $obj;
        });

        self::assertEquals('foo', $this->container->resolve('foo')->foo);
        self::assertEquals('baz', $this->container->resolve('foo')->bar);
        self::assertEquals('foo', $this->container->resolve('foo')->baz);
    }

    public function testExtendCanBeCalledBeforeBind(): void
    {
        $this->container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });
        $this->container['foo'] = 'foo';

        self::assertEquals('foobar', $this->container->resolve('foo'));
    }

    public function testParametersCanBePassedThroughToClosure(): void
    {
        $this->container->bind('foo', function ($container, $a, $b, $c) {
            return [$a, $b, $c];
        });

        self::assertEquals([1, 2, 3], $this->container->resolve('foo', [1, 2, 3]));
    }

    public function testResolutionOfDefaultParameters(): void
    {
        $instance = $this->container->resolve(ContainerDefaultValueFixture::class);

        self::assertInstanceOf(ContainerConcreteFixture::class, $instance->stub);
        self::assertEquals('narrowspark', $instance->default);
    }

    public function testUnsetRemoveBoundInstances(): void
    {
        $this->container->instance('object', new stdClass());
        unset($this->container['object']);

        self::assertFalse($this->container->has('object'));

        $this->container->instance('object', new stdClass());
        $this->container->forget('object');

        self::assertFalse($this->container->has('object'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\ContainerException
     * @expectedExceptionMessage The id parameter must be of type string, [stdClass] given.
     */
    public function testHasToThrowExceptionOnNoStringType(): void
    {
        self::assertFalse($this->container->has(new stdClass()));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\ContainerException
     * @expectedExceptionMessage The id parameter must be of type string, [stdClass] given.
     */
    public function testGetToThrowExceptionOnNoStringType(): void
    {
        self::assertFalse($this->container->get(new stdClass()));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\NotFoundException
     * @expectedExceptionMessage Abstract [test] is not being managed by the container.
     */
    public function testGetToThrowExceptionOnNotFoundId(): void
    {
        self::assertFalse($this->container->get('test'));
    }

    public function testBoundInstanceAndAliasCheckViaArrayAccess(): void
    {
        $this->container->instance('object', new stdClass());
        $this->container->alias('object', 'alias');

        self::assertTrue(isset($this->container['object']));
        self::assertTrue(isset($this->container['alias']));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @expectedExceptionMessage Circular reference found while resolving [Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubD].
     */
    public function testCircularReferenceCheck(): void
    {
        // Since the dependency is ( D -> F -> E -> D ), the exception
        // message should state that the issue starts in class D
        $this->container->resolve(ContainerCircularReferenceStubD::class);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @expectedExceptionMessage Circular reference found while resolving [Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubB].
     */
    public function testCircularReferenceCheckDetectCycleStartLocation(): void
    {
        // Since the dependency is ( A -> B -> C -> B ), the exception
        // message should state that the issue starts in class B
        $this->container->resolve(ContainerCircularReferenceStubA::class);
    }

    public function testContainerCanInjectSimpleVariable(): void
    {
        $this->container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $this->container->resolve(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);

        $this->container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')->give(function ($container) {
                return $this->container->resolve(ContainerConcreteFixture::class);
            });

        $instance = $this->container->resolve(ContainerInjectVariableFixture::class);

        self::assertInstanceOf(ContainerConcreteFixture::class, $instance->something);
    }

    public function testContainerCanInjectSimpleVariableToBinding(): void
    {
        $this->container->bind(ContainerInjectVariableFixture::class);

        $this->container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $this->container->resolve(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException
     * @expectedExceptionMessage Parameter [something] cannot be injected in [Viserio\Component\Container\Tests\Fixture\ContainerInjectVariableFixture].
     */
    public function testContainerWhenNeedsGiveToThrowException(): void
    {
        $this->container->when(ContainerInjectVariableFixture::class . '::set')
            ->needs(ContainerConcreteFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $this->container->resolve(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\ContainerTestNotResolvable] is not resolvable.
     */
    public function testContainerCantInjectObjectIsNotResolvable(): void
    {
        $this->container->when('Viserio\Component\Container\Tests\ContainerTestNotResolvable')
            ->needs(ContainerConcreteFixture::class)
            ->give(100);

        $instance = $this->container->resolve(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\Fixture\ContainerTestNoConstructor] must have a constructor.
     */
    public function testContainerCantInjectObjectWithoutConstructor(): void
    {
        $this->container->when(ContainerTestNoConstructor::class)
            ->needs(ContainerConcreteFixture::class)
            ->give(100);

        $instance = $this->container->resolve(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    public function testContainerCanInjectDifferentImplementationsDependingOnContext(): void
    {
        $this->container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationFixture::class);

        $this->container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationTwoFixture::class);

        $one = $this->container->resolve(ContainerTestContextInjectOneFixture::class);
        $two = $this->container->resolve(ContainerTestContextInjectTwoFixture::class);

        self::assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        self::assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);

        // Test With Closures
        $this->container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationFixture::class);
        $this->container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(function ($container) {
                return $this->container->resolve(ContainerImplementationTwoFixture::class);
            });

        $one = $this->container->resolve(ContainerTestContextInjectOneFixture::class);
        $two = $this->container->resolve(ContainerTestContextInjectTwoFixture::class);

        self::assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        self::assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @expectedExceptionMessage Unresolvable dependency resolving [Parameter #0 [ <required> $first ]] in [Viserio\Component\Container\Tests\Fixture\ContainerMixedPrimitiveFixture]
     */
    public function testInternalClassWithDefaultParameters(): void
    {
        $this->container->resolve(ContainerMixedPrimitiveFixture::class);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @expectedExceptionMessage Unable to reflect on the class [Viserio\Component\Container\Tests\Fixture\ContainerPrivateConstructor], does the class exist and is it properly autoloaded?
     */
    public function testUnableToReflectClass(): void
    {
        $this->container->resolve(ContainerPrivateConstructor::class);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\Fixture\ContainerContractFixtureInterface] is not resolvable. Build stack : []
     */
    public function testBindingResolutionExceptionMessage(): void
    {
        $this->container->resolve(ContainerContractFixtureInterface::class);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\Fixture\ContainerContractFixtureInterface] is not resolvable. Build stack : [Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectOneFixture]
     */
    public function testBindingResolutionExceptionMessageIncludesBuildStack(): void
    {
        $this->container->resolve(ContainerTestContextInjectOneFixture::class);
    }

    public function testDelegateContainer(): void
    {
        $delegate = new DIContainer();
        $delegate->set('instance', function () {
            return 'value';
        });

        $this->container->delegate($delegate);
        $this->container->instance('instance2', $this->container->get('instance'));

        self::assertSame('value', $this->container->get('instance2'));
        self::assertTrue($this->container->hasInDelegate('instance'));
        self::assertFalse($this->container->hasInDelegate('instance3'));
    }

    public function testExtendedBindingsKeptTypes(): void
    {
        $this->container->singleton('foo', function () {
            return (object) ['name' => 'narrowspark'];
        });

        $this->container->extend('foo', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $this->container->bind('bar', function () {
            return (object) ['name' => 'narrowspark'];
        });

        $this->container->extend('bar', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        self::assertSame($this->container->resolve('foo'), $this->container->resolve('foo'));
        self::assertNotSame($this->container->resolve('bar'), $this->container->resolve('bar'));
    }

    public function testExtendIsLazyInitialized(): void
    {
        ContainerLazyExtendFixture::$initialized = false;

        $this->container->bind(ContainerLazyExtendFixture::class);
        $this->container->extend(ContainerLazyExtendFixture::class, function ($obj, $container) {
            $obj->init();

            return $obj;
        });

        self::assertFalse(ContainerLazyExtendFixture::$initialized);

        $this->container->resolve(ContainerLazyExtendFixture::class);

        self::assertTrue(ContainerLazyExtendFixture::$initialized);
    }

    public function testContextualBindingWorksForExistingInstancedBindings(): void
    {
        $this->container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());

        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationTwoFixture::class);

        self::assertInstanceOf(
            ContainerImplementationTwoFixture::class,
            $this->container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksForNewlyInstancedBindings(): void
    {
        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $this->container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $this->container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnExistingAliasedInstances(): void
    {
        $this->container->instance('stub', new ContainerImplementationFixture());
        $this->container->alias('stub', ContainerContractFixtureInterface::class);

        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $this->container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnNewAliasedInstances(): void
    {
        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $this->container->instance('stub', new ContainerImplementationFixture());
        $this->container->alias('stub', ContainerContractFixtureInterface::class);

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $this->container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnNewAliasedBindings(): void
    {
        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $this->container->bind('stub', ContainerImplementationFixture::class);
        $this->container->alias('stub', ContainerContractFixtureInterface::class);

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $this->container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingDoesntOverrideNonContextualResolution(): void
    {
        $this->container->instance('stub', new ContainerImplementationFixture());
        $this->container->alias('stub', ContainerContractFixtureInterface::class);

        $this->container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectInstantiationsFixture::class);

        self::assertInstanceOf(
            ContainerTestContextInjectInstantiationsFixture::class,
            $this->container->resolve(ContainerTestContextInjectTwoFixture::class)->impl
        );

        self::assertInstanceOf(
            ContainerImplementationFixture::class,
            $this->container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextuallyBoundInstancesAreNotUnnecessarilyRecreated(): void
    {
        ContainerTestContextInjectInstantiationsFixture::$instantiations = 0;

        $this->container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());
        $this->container->instance('ContainerTestContextInjectInstantiationsFixture', new ContainerTestContextInjectInstantiationsFixture());

        self::assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);

        $this->container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give('ContainerTestContextInjectInstantiationsFixture');

        $this->container->resolve(ContainerTestContextInjectOneFixture::class);
        $this->container->resolve(ContainerTestContextInjectOneFixture::class);
        $this->container->resolve(ContainerTestContextInjectOneFixture::class);
        $this->container->resolve(ContainerTestContextInjectOneFixture::class);

        self::assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException
     * @expectedExceptionMessage Parameter [stub] cannot be injected in [Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectOneFixture].
     */
    public function testContextualBindingNotWorksOnBoundAlias(): void
    {
        $this->container->alias(ContainerContractFixtureInterface::class, 'stub');
        $this->container->bind('stub', ContainerImplementationFixture::class);

        $this->container->when(ContainerTestContextInjectOneFixture::class)->needs('stub')->give(ContainerImplementationTwoFixture::class);

        $this->container->get(ContainerTestContextInjectOneFixture::class);
    }

    public function testProvider(): void
    {
        $this->container->register(new SimpleFixtureServiceProvider());

        self::assertEquals('value', $this->container['param']);
        self::assertInstanceOf(ServiceFixture::class, $this->container['service']);
    }

    public function testTaggedProvider(): void
    {
        $this->container->register(new SimpleTaggedServiceProvider());

        self::assertSame('value', $this->container['param']);

        $array = $this->container->getTagged('test');

        self::assertSame('value', $array[0]);
    }

    public function testProviderWithRegisterMethod(): void
    {
        $this->container->register(new SimpleFixtureServiceProvider(), [
            'anotherParameter' => 'anotherValue',
        ]);

        self::assertEquals('value', $this->container->get('param'));
        self::assertEquals('anotherValue', $this->container->get('anotherParameter'));
        self::assertInstanceOf(ServiceFixture::class, $this->container->get('service'));
    }

    public function testExtendingValue(): void
    {
        $this->container->instance('previous', 'foo');
        $this->container->register(new SimpleFixtureServiceProvider());

        self::assertEquals('foofoo', $this->container->get('previous'));
    }

    public function testExtendingNothing(): void
    {
        $this->container->register(new SimpleFixtureServiceProvider());

        self::assertSame('', $this->container->get('previous'));
    }

    public function testTag(): void
    {
        $this->container->instance('adapterA', 'a');
        $this->container->instance('adapterB', 'b');
        $this->container->instance('adapterC', 'c');

        $this->container->tag('test', ['adapterA', 'adapterB', 'adapterC']);

        $array = $this->container->getTagged('test');

        self::assertSame('a', $array[0]);
        self::assertSame('b', $array[1]);
        self::assertSame('c', $array[2]);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Container\Exception\InvalidArgumentException
     * @expectedExceptionMessage The tag name must be a non-empty string.
     */
    public function testTagToThrowExceptionOnEmptyString(): void
    {
        $this->container->tag('', []);
    }

    public function testReset(): void
    {
        $this->container->instance('test', 'value');

        $this->container->reset();

        try {
            $this->container->get('test');
            $this->fail('this should not happened');
        } catch (NotFoundException $exception) {
            self::assertSame('Abstract [test] is not being managed by the container.', $exception->getMessage());
        }

        self::assertSame([], $this->container->getBindings());
    }

    public function testBindIf(): void
    {
        $this->container->instance('test', 'value');

        $this->container->bindIf('test', 'foo');

        self::assertSame('value', $this->container->get('test'));

        $this->container->bindIf('foo', 'foo');

        try {
            $this->container->get('foo');
            $this->fail('this should not happened');
        } catch (BindingResolutionException $exception) {
            self::assertSame('[foo] is not resolvable. Build stack : [].', $exception->getMessage());
        }
    }
}
