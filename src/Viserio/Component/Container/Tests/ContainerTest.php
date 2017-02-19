<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use Mouf\Picotainer\Picotainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
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

class ContainerTest extends TestCase
{
    use MockeryTrait;

    /**
     * @var \Viserio\Component\Container\Container
     */
    protected $container = null;

    /**
     * @var array
     */
    protected $services = [];

    public function setUp()
    {
        $this->container = new Container();

        $this->services = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];

        foreach (array_keys($this->services) as $id) {
            $service     = new stdClass();
            $service->id = $id;

            $this->services[$id] = $service;
            $this->container->bind($id, $service);
        }
    }

    public function testClosureResolution()
    {
        $container = $this->container;
        $container->bind('name', function () {
            return 'Narrowspark';
        });

        self::assertEquals('Narrowspark', $container->make('name'));
    }

    public function testBindIfDoesntRegisterIfServiceAlreadyRegistered()
    {
        $container = new Container();

        $container->bind('name', function () {
            return 'Narrowspark';
        });

        $container->bindIf('name', function () {
            return 'Viserio';
        });

        self::assertEquals('Narrowspark', $container->make('name'));
    }

    public function testSharedClosureResolution()
    {
        $container = $this->container;
        $class     = new stdClass();

        $container->singleton('class', function () use ($class) {
            return $class;
        });

        self::assertSame($class, $container->make('class'));
    }

    public function testAutoConcreteResolution()
    {
        $container = $this->container;

        self::assertInstanceOf(ContainerConcreteFixture::class, $container->make(ContainerConcreteFixture::class));
    }

    public function testResolveMethod()
    {
        $container = new Container();

        self::assertEquals('Hello', $container->make(FactoryClass::class . '::create'));
    }

    public function testResolveMethodFromString()
    {
        $container = new Container();

        self::assertEquals('Hello', $container->resolveMethod(FactoryClass::class . '::staticCreate'));
        self::assertEquals('Hello', $container->resolveMethod(FactoryClass::class . '::staticCreateWitArg', ['name' => 'Hello']));
    }

    public function testSharedConcreteResolution()
    {
        $container = $this->container;
        $container->singleton(ContainerConcreteFixture::class);

        $var1 = $container->make(ContainerConcreteFixture::class);
        $var2 = $container->make(ContainerConcreteFixture::class);

        self::assertSame($var1, $var2);
    }

    public function testParametersCanOverrideDependencies()
    {
        $container = new Container();
        $mock      = $this->mock(ContainerContractFixtureInterface::class);
        $stub      = new ContainerDependentFixture($mock);
        $resolved  = $container->make(ContainerNestedDependentFixture::class, [$stub]);

        self::assertInstanceOf(ContainerNestedDependentFixture::class, $resolved);
        self::assertEquals($mock, $resolved->inner->impl);
    }

    public function testResolveNonBoundWithClosure()
    {
        $container = new Container();

        $class = $container->resolveNonBound(function ($container) {
            return $container;
        });

        self::assertInstanceOf(Container::class, $class);
    }

    public function testAbstractToConcreteResolution()
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $class = $container->make(ContainerDependentFixture::class);

        self::assertInstanceOf(ContainerImplementationFixture::class, $class->impl);
    }

    public function testNestedDependencyResolution()
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $class = $container->make(ContainerNestedDependentFixture::class);

        self::assertInstanceOf(ContainerDependentFixture::class, $class->inner);
        self::assertInstanceOf(ContainerImplementationFixture::class, $class->inner->impl);
    }

    public function testContainerIsPassedToResolvers()
    {
        $container = $this->container;
        $container->bind('something', function ($c) {
            return $c;
        });

        $c = $container->make('something');

        self::assertSame($c, $container);
    }

    public function testArrayAccess()
    {
        $container              = $this->container;
        $container['something'] = function () {
            return 'foo';
        };

        self::assertTrue(isset($container['something']));
        self::assertEquals('foo', $container['something']);

        unset($container['something']);

        self::assertFalse(isset($container['something']));

        $container['foo'] = 'foo';
        $result           = $container->make('foo');

        self::assertSame($result, $container->make('foo'));
    }

    public function testAliases()
    {
        $container        = new Container();
        $container['foo'] = 'bar';
        $container->alias('foo', 'baz');
        $container->alias('baz', 'bat');

        self::assertEquals('bar', $container->make('foo'));
        self::assertEquals('bar', $container->make('baz'));
        self::assertEquals('bar', $container->make('bat'));
    }

    public function testBindingsCanBeOverridden()
    {
        $container        = $this->container;
        $container['foo'] = 'bar';
        $foo              = $container['foo'];
        $container['foo'] = 'baz';

        self::assertEquals('baz', $container['foo']);
    }

    public function testExtendedBindings()
    {
        $container        = $this->container;
        $container['foo'] = 'foo';
        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        self::assertEquals('foobar', $container->make('foo'));

        $container        = $this->container;
        $container['foo'] = function () {
            return (object) ['name' => 'narrowspark'];
        };

        $container->extend('foo', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $result = $container->make('foo');

        self::assertEquals('narrowspark', $result->name);
        self::assertEquals('viserio', $result->oldName);
    }

    public function testMultipleExtends()
    {
        $container        = $this->container;
        $container['foo'] = 'foo';

        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        $container->extend('foo', function ($old, $container) {
            return $old . 'baz';
        });

        self::assertEquals('foobarbaz', $container->make('foo'));
    }

    public function testExtendInstancesArePreserved()
    {
        $container = new Container();
        $container->bind('foo', function () {
            $obj = new stdClass();
            $obj->foo = 'bar';

            return $obj;
        });

        $obj      = new stdClass();
        $obj->foo = 'foo';

        $container->instance('foo', $obj);
        $container->extend('foo', function ($obj, $container) {
            $obj->bar = 'baz';

            return $obj;
        });
        $container->extend('foo', function ($obj, $container) {
            $obj->baz = 'foo';

            return $obj;
        });

        self::assertEquals('foo', $container->make('foo')->foo);
        self::assertEquals('baz', $container->make('foo')->bar);
        self::assertEquals('foo', $container->make('foo')->baz);
    }

    public function testExtendCanBeCalledBeforeBind()
    {
        $container = $this->container;
        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });
        $container['foo'] = 'foo';

        self::assertEquals('foobar', $container->make('foo'));
    }

    public function testParametersCanBePassedThroughToClosure()
    {
        $container = $this->container;
        $container->bind('foo', function ($container, $a, $b, $c) {
            return [$a, $b, $c];
        });

        self::assertEquals([1, 2, 3], $container->make('foo', [1, 2, 3]));
    }

    public function testResolutionOfDefaultParameters()
    {
        $container = new Container();
        $instance  = $container->make(ContainerDefaultValueFixture::class);

        self::assertInstanceOf(ContainerConcreteFixture::class, $instance->stub);
        self::assertEquals('narrowspark', $instance->default);
    }

    public function testUnsetRemoveBoundInstances()
    {
        $container = new Container();
        $container->instance('object', new stdClass());
        unset($container['object']);

        self::assertFalse($container->has('object'));

        $container->instance('object', new stdClass());
        $container->forget('object');

        self::assertFalse($container->has('object'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\ContainerException
     * @expectedExceptionMessage The name parameter must be of type string, [stdClass] given.
     */
    public function testHasToThrowExceptionOnNoStringType()
    {
        $container = new Container();

        self::assertFalse($container->has(new stdClass()));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\ContainerException
     * @expectedExceptionMessage The id parameter must be of type string, [stdClass] given.
     */
    public function testGetToThrowExceptionOnNoStringType()
    {
        $container = new Container();

        self::assertFalse($container->get(new stdClass()));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\NotFoundException
     * @expectedExceptionMessage Abstract (test) is not being managed by the container
     */
    public function testGetToThrowExceptionOnNotFoundId()
    {
        $container = new Container();

        self::assertFalse($container->get('test'));
    }

    public function testBoundInstanceAndAliasCheckViaArrayAccess()
    {
        $container = new Container();
        $container->instance('object', new stdClass());
        $container->alias('object', 'alias');

        self::assertTrue(isset($container['object']));
        self::assertTrue(isset($container['alias']));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\CyclicDependencyException
     * @expectedExceptionMessage Circular reference found while resolving [Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubD].
     */
    public function testCircularReferenceCheck()
    {
        // Since the dependency is ( D -> F -> E -> D ), the exception
        // message should state that the issue starts in class D
        $container = $this->container;
        $container->make(ContainerCircularReferenceStubD::class);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\CyclicDependencyException
     * @expectedExceptionMessage Circular reference found while resolving [Viserio\Component\Container\Tests\Fixture\ContainerCircularReferenceStubB].
     */
    public function testCircularReferenceCheckDetectCycleStartLocation()
    {
        // Since the dependency is ( A -> B -> C -> B ), the exception
        // message should state that the issue starts in class B
        $container = $this->container;
        $container->make(ContainerCircularReferenceStubA::class);
    }

    public function testContainerCanInjectSimpleVariable()
    {
        $container = new Container();
        $container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $container->make(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);

        $container = new Container();
        $container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')->give(function ($container) {
                return $container->make(ContainerConcreteFixture::class);
            });

        $instance = $container->make(ContainerInjectVariableFixture::class);

        self::assertInstanceOf(ContainerConcreteFixture::class, $instance->something);
    }

    public function testContainerCanInjectSimpleVariableToBinding()
    {
        $container = new Container();
        $container->bind(ContainerInjectVariableFixture::class);

        $container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $container->make(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\UnresolvableDependencyException
     * @expectedExceptionMessage Parameter [string] cannot be injected in [array].
     */
    public function testContainerWhenNeedsGiveToThrowException()
    {
        $container = new Container();

        $container->when(ContainerInjectVariableFixture::class . '::set')
            ->needs(ContainerConcreteFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $container->make(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\UnresolvableDependencyException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\ContainerTestNotResolvable] is not resolvable.
     */
    public function testContainerCantInjectObjectIsNotResolvable()
    {
        $container = new Container();

        $container->when(ContainerTestNotResolvable::class)
            ->needs(ContainerConcreteFixture::class)
            ->give(100);
        $instance = $container->make(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\UnresolvableDependencyException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\Fixture\ContainerTestNoConstructor] must have a constructor.
     */
    public function testContainerCantInjectObjectWithoutConstructor()
    {
        $container = new Container();

        $container->when(ContainerTestNoConstructor::class)
            ->needs(ContainerConcreteFixture::class)
            ->give(100);
        $instance = $container->make(ContainerInjectVariableFixture::class);

        self::assertEquals(100, $instance->something);
    }

    public function testContainerCanInjectDifferentImplementationsDependingOnContext()
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationFixture::class);

        $container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationTwoFixture::class);

        $one = $container->make(ContainerTestContextInjectOneFixture::class);
        $two = $container->make(ContainerTestContextInjectTwoFixture::class);

        self::assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        self::assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);

        /*
         * Test With Closures
         */
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationFixture::class);
        $container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(function ($container) {
                return $container->make(ContainerImplementationTwoFixture::class);
            });

        $one = $container->make(ContainerTestContextInjectOneFixture::class);
        $two = $container->make(ContainerTestContextInjectTwoFixture::class);

        self::assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        self::assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage Unresolvable dependency resolving [Parameter #0 [ <required> $first ]] in [Viserio\Component\Container\Tests\Fixture\ContainerMixedPrimitiveFixture]
     */
    public function testInternalClassWithDefaultParameters()
    {
        $container = new Container();
        $container->make(ContainerMixedPrimitiveFixture::class);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage Unable to reflect on the class [string], does the class exist and is it properly autoloaded?
     */
    public function testUnableToReflectClass()
    {
        $container = new Container();
        $container->make(ContainerPrivateConstructor::class);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\Fixture\ContainerContractFixtureInterface] is not resolvable. Build stack : []
     */
    public function testBindingResolutionExceptionMessage()
    {
        $container = new Container();
        $container->make(ContainerContractFixtureInterface::class);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage [Viserio\Component\Container\Tests\Fixture\ContainerContractFixtureInterface] is not resolvable. Build stack : [Viserio\Component\Container\Tests\Fixture\ContainerTestContextInjectOneFixture]
     */
    public function testBindingResolutionExceptionMessageIncludesBuildStack()
    {
        $container = new Container();
        $container->make(ContainerTestContextInjectOneFixture::class);
    }

    public function testDelegateContainer()
    {
        $picotainer = new Picotainer([
            'instance' => function () {
                return 'value';
            },
        ]);

        $container = new Container();
        $container->delegate($picotainer);
        $container->instance('instance2', $container->get('instance'));

        self::assertSame('value', $container->get('instance2'));
        self::assertTrue($container->hasInDelegate('instance'));
        self::assertFalse($container->hasInDelegate('instance3'));
    }

    public function testExtendedBindingsKeptTypes()
    {
        $container = new Container();

        $container->singleton('foo', function () {
            return (object) ['name' => 'narrowspark'];
        });

        $container->extend('foo', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $container->bind('bar', function () {
            return (object) ['name' => 'narrowspark'];
        });

        $container->extend('bar', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        self::assertSame($container->make('foo'), $container->make('foo'));
        self::assertNotSame($container->make('bar'), $container->make('bar'));
    }

    public function testExtendIsLazyInitialized()
    {
        ContainerLazyExtendFixture::$initialized = false;

        $container = new Container();
        $container->bind(ContainerLazyExtendFixture::class);
        $container->extend(ContainerLazyExtendFixture::class, function ($obj, $container) {
            $obj->init();

            return $obj;
        });
        self::assertFalse(ContainerLazyExtendFixture::$initialized);
        $container->make(ContainerLazyExtendFixture::class);
        self::assertTrue(ContainerLazyExtendFixture::$initialized);
    }

    public function testContextualBindingWorksForExistingInstancedBindings()
    {
        $container = new Container();

        $container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationTwoFixture::class);

        self::assertInstanceOf(
            ContainerImplementationTwoFixture::class,
            $container->make(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksForNewlyInstancedBindings()
    {
        $container = new Container();

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->make(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnExistingAliasedInstances()
    {
        $container = new Container();

        $container->instance('stub', new ContainerImplementationFixture());
        $container->alias('stub', ContainerContractFixtureInterface::class);

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->make(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnNewAliasedInstances()
    {
        $container = new Container();

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $container->instance('stub', new ContainerImplementationFixture());
        $container->alias('stub', ContainerContractFixtureInterface::class);

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->make(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnNewAliasedBindings()
    {
        $container = new Container();

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $container->bind('stub', ContainerImplementationFixture::class);
        $container->alias('stub', ContainerContractFixtureInterface::class);

        self::assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->make(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingDoesntOverrideNonContextualResolution()
    {
        $container = new Container();

        $container->instance('stub', new ContainerImplementationFixture());
        $container->alias('stub', ContainerContractFixtureInterface::class);

        $container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectInstantiationsFixture::class);

        self::assertInstanceOf(
            ContainerTestContextInjectInstantiationsFixture::class,
            $container->make(ContainerTestContextInjectTwoFixture::class)->impl
        );

        self::assertInstanceOf(
            ContainerImplementationFixture::class,
            $container->make(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextuallyBoundInstancesAreNotUnnecessarilyRecreated()
    {
        ContainerTestContextInjectInstantiationsFixture::$instantiations = 0;

        $container = new Container();

        $container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());
        $container->instance('ContainerTestContextInjectInstantiationsFixture', new ContainerTestContextInjectInstantiationsFixture());

        self::assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give('ContainerTestContextInjectInstantiationsFixture');

        $container->make(ContainerTestContextInjectOneFixture::class);
        $container->make(ContainerTestContextInjectOneFixture::class);
        $container->make(ContainerTestContextInjectOneFixture::class);
        $container->make(ContainerTestContextInjectOneFixture::class);

        self::assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);
    }
}
