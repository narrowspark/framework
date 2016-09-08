<?php
declare(strict_types=1);
namespace Viserio\Container\Tests;

use Mouf\Picotainer\Picotainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use StdClass;
use Viserio\Container\Container;
use Viserio\Container\Tests\Fixture\ContainerCircularReferenceStubA;
use Viserio\Container\Tests\Fixture\ContainerCircularReferenceStubD;
use Viserio\Container\Tests\Fixture\ContainerConcreteFixture;
use Viserio\Container\Tests\Fixture\ContainerContractFixtureInterface;
use Viserio\Container\Tests\Fixture\ContainerDefaultValueFixture;
use Viserio\Container\Tests\Fixture\ContainerDependentFixture;
use Viserio\Container\Tests\Fixture\ContainerImplementationFixture;
use Viserio\Container\Tests\Fixture\ContainerImplementationTwoFixture;
use Viserio\Container\Tests\Fixture\ContainerInjectVariableFixture;
use Viserio\Container\Tests\Fixture\ContainerMixedPrimitiveFixture;
use Viserio\Container\Tests\Fixture\ContainerNestedDependentFixture;
use Viserio\Container\Tests\Fixture\ContainerPrivateConstructor;
use Viserio\Container\Tests\Fixture\ContainerTestContextInjectOneFixture;
use Viserio\Container\Tests\Fixture\ContainerTestContextInjectTwoFixture;
use Viserio\Container\Tests\Fixture\FactoryClass;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \Viserio\Container\Container
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
            $service = new StdClass();
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

        $this->assertEquals('Narrowspark', $container->make('name'));
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

        $this->assertEquals('Narrowspark', $container->make('name'));
    }

    public function testSharedClosureResolution()
    {
        $container = $this->container;
        $class = new StdClass();

        $container->singleton('class', function () use ($class) {
            return $class;
        });

        $this->assertSame($class, $container->make('class'));
    }

    public function testAutoConcreteResolution()
    {
        $container = $this->container;

        $this->assertInstanceOf(ContainerConcreteFixture::class, $container->make(ContainerConcreteFixture::class));
    }

    public function testSlashesAreHandled()
    {
        $container = new Container();

        $container->bind('\Foo', function () {
            return 'hello';
        });

        $this->assertEquals('hello', $container->make('Foo'));
    }

    public function testResolveMethod()
    {
        $container = new Container();

        $this->assertEquals('Hello', $container->make(FactoryClass::class . '::create'));
    }

    public function testSharedConcreteResolution()
    {
        $container = $this->container;
        $container->singleton(ContainerConcreteFixture::class);

        $var1 = $container->make(ContainerConcreteFixture::class);
        $var2 = $container->make(ContainerConcreteFixture::class);

        $this->assertSame($var1, $var2);
    }

    public function testParametersCanOverrideDependencies()
    {
        $container = new Container();
        $mock = $this->mock(ContainerContractFixtureInterface::class);
        $stub = new ContainerDependentFixture($mock);
        $resolved = $container->make(ContainerNestedDependentFixture::class, [$stub]);

        $this->assertInstanceOf(ContainerNestedDependentFixture::class, $resolved);
        $this->assertEquals($mock, $resolved->inner->impl);
    }

    public function testAbstractToConcreteResolution()
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $class = $container->make(ContainerDependentFixture::class);

        $this->assertInstanceOf(ContainerImplementationFixture::class, $class->impl);
    }

    public function testNestedDependencyResolution()
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $class = $container->make(ContainerNestedDependentFixture::class);

        $this->assertInstanceOf(ContainerDependentFixture::class, $class->inner);
        $this->assertInstanceOf(ContainerImplementationFixture::class, $class->inner->impl);
    }

    public function testContainerIsPassedToResolvers()
    {
        $container = $this->container;
        $container->bind('something', function ($c) {
            return $c;
        });

        $c = $container->make('something');

        $this->assertSame($c, $container);
    }

    public function testArrayAccess()
    {
        $container = $this->container;
        $container['something'] = function () {
            return 'foo';
        };

        $this->assertTrue(isset($container['something']));
        $this->assertEquals('foo', $container['something']);

        unset($container['something']);

        $this->assertFalse(isset($container['something']));

        $container['foo'] = 'foo';
        $result = $container->make('foo');

        $this->assertSame($result, $container->make('foo'));
    }

    public function testAliases()
    {
        $container = new Container();
        $container['foo'] = 'bar';
        $container->alias('foo', 'baz');
        $container->alias('baz', 'bat');

        $this->assertEquals('bar', $container->make('foo'));
        $this->assertEquals('bar', $container->make('baz'));
        $this->assertEquals('bar', $container->make('bat'));

        $container->bind(['bam' => 'boom'], function () {
            return 'pow';
        });

        $this->assertEquals('pow', $container->make('bam'));
        $this->assertEquals('pow', $container->make('boom'));

        $container->instance(['zoom' => 'zing'], 'wow');

        $this->assertEquals('wow', $container->make('zoom'));
        $this->assertEquals('wow', $container->make('zing'));
    }

    public function testBindingsCanBeOverridden()
    {
        $container = $this->container;
        $container['foo'] = 'bar';
        $foo = $container['foo'];
        $container['foo'] = 'baz';

        $this->assertEquals('baz', $container['foo']);
    }

    public function testExtendedBindings()
    {
        $container = $this->container;
        $container['foo'] = 'foo';
        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        $this->assertEquals('foobar', $container->make('foo'));

        $container = $this->container;
        $container['foo'] = function () {
            return (object) ['name' => 'narrowspark'];
        };

        $container->extend('foo', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $result = $container->make('foo');

        $this->assertEquals('narrowspark', $result->name);
        $this->assertEquals('viserio', $result->oldName);
    }

    public function testMultipleExtends()
    {
        $container = $this->container;
        $container['foo'] = 'foo';

        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        $container->extend('foo', function ($old, $container) {
            return $old . 'baz';
        });

        $this->assertEquals('foobarbaz', $container->make('foo'));
    }

    public function testExtendInstancesArePreserved()
    {
        $container = new Container();
        $container->bind('foo', function () {
            $obj = new StdClass();
            $obj->foo = 'bar';

            return $obj;
        });

        $obj = new StdClass();
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

        $this->assertEquals('foo', $container->make('foo')->foo);
        $this->assertEquals('baz', $container->make('foo')->bar);
        $this->assertEquals('foo', $container->make('foo')->baz);
    }

    public function testExtendCanBeCalledBeforeBind()
    {
        $container = $this->container;
        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });
        $container['foo'] = 'foo';

        $this->assertEquals('foobar', $container->make('foo'));
    }

    public function testParametersCanBePassedThroughToClosure()
    {
        $container = $this->container;
        $container->bind('foo', function ($container, $a, $b, $c) {
            return [$a, $b, $c];
        });

        $this->assertEquals([1, 2, 3], $container->make('foo', [1, 2, 3]));
    }

    public function testResolutionOfDefaultParameters()
    {
        $container = new Container();
        $instance = $container->make(ContainerDefaultValueFixture::class);

        $this->assertInstanceOf(ContainerConcreteFixture::class, $instance->stub);
        $this->assertEquals('narrowspark', $instance->default);
    }

    public function testUnsetRemoveBoundInstances()
    {
        $container = new Container();
        $container->instance('object', new StdClass());
        unset($container['object']);

        $this->assertFalse($container->has('object'));

        $container->instance('object', new StdClass());
        $container->forget('object');

        $this->assertFalse($container->has('object'));
    }

    public function testBoundInstanceAndAliasCheckViaArrayAccess()
    {
        $container = new Container();
        $container->instance('object', new StdClass());
        $container->alias('object', 'alias');

        $this->assertTrue(isset($container['object']));
        $this->assertTrue(isset($container['alias']));
    }

    /**
     * @expectedException \Viserio\Contracts\Container\Exceptions\CyclicDependencyException
     * @expectedExceptionMessage Circular reference found while resolving [Viserio\Container\Tests\Fixture\ContainerCircularReferenceStubD].
     */
    public function testCircularReferenceCheck()
    {
        // Since the dependency is ( D -> F -> E -> D ), the exception
        // message should state that the issue starts in class D
        $container = $this->container;
        $container->make(ContainerCircularReferenceStubD::class);
    }

    /**
     * @expectedException \Viserio\Contracts\Container\Exceptions\CyclicDependencyException
     * @expectedExceptionMessage Circular reference found while resolving [Viserio\Container\Tests\Fixture\ContainerCircularReferenceStubB].
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

        $this->assertEquals(100, $instance->something);

        $container = new Container();
        $container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')->give(function ($container) {
                return $container->make(ContainerConcreteFixture::class);
            });

        $instance = $container->make(ContainerInjectVariableFixture::class);

        $this->assertInstanceOf(ContainerConcreteFixture::class, $instance->something);
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

        $this->assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        $this->assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);

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

        $this->assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        $this->assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);
    }

    public function testContextualBindingWorksRegardlessOfLeadingBackslash()
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $container->when('\Viserio\Container\Tests\Fixture\ContainerTestContextInjectOneFixture')
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationTwoFixture::class);
        $container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs('\Viserio\Container\Tests\Fixture\ContainerContractFixtureInterface')
            ->give(ContainerImplementationTwoFixture::class);

        $this->assertInstanceOf(
            ContainerImplementationTwoFixture::class,
            $container->make(ContainerTestContextInjectOneFixture::class)->impl
        );
        $this->assertInstanceOf(
            ContainerImplementationTwoFixture::class,
            $container->make(ContainerTestContextInjectTwoFixture::class)->impl
        );
        $this->assertInstanceOf(
            ContainerImplementationTwoFixture::class,
            $container->make('\Viserio\Container\Tests\Fixture\ContainerTestContextInjectTwoFixture')->impl
        );
    }

    /**
     * @expectedException \Viserio\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage Unresolvable dependency resolving [Parameter #0 [ <required> $first ]] in [Viserio\Container\Tests\Fixture\ContainerMixedPrimitiveFixture]
     */
    public function testInternalClassWithDefaultParameters()
    {
        $container = new Container();
        $container->make(ContainerMixedPrimitiveFixture::class);
    }

    /**
     * @expectedException \Viserio\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage Unable to reflect on the class [string], does the class exist and is it properly autoloaded?
     */
    public function testUnableToReflectClass()
    {
        $container = new Container();
        $container->make(ContainerPrivateConstructor::class);
    }

    /**
     * @expectedException \Viserio\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage [Viserio\Container\Tests\Fixture\ContainerContractFixtureInterface] is not resolvable. Build stack : []
     */
    public function testBindingResolutionExceptionMessage()
    {
        $container = new Container();
        $container->make(ContainerContractFixtureInterface::class);
    }

    /**
     * @expectedException \Viserio\Contracts\Container\Exceptions\BindingResolutionException
     * @expectedExceptionMessage [Viserio\Container\Tests\Fixture\ContainerContractFixtureInterface] is not resolvable. Build stack : [Viserio\Container\Tests\Fixture\ContainerTestContextInjectOneFixture]
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

        $this->assertSame('value', $container->get('instance2'));
        $this->assertTrue($container->hasInDelegate('instance'));
    }

    public function testExtendedBindingsKeptTypes()
    {
        $container = new Container();

        $container->singleton(['foo' => 'foo2'], function () {
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

        $this->assertSame($container->make('foo'), $container->make('foo'));
        $this->assertSame($container->make('foo'), $container->make('foo2'));
        $this->assertNotSame($container->make('bar'), $container->make('bar'));
    }
}
