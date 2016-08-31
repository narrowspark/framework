<?php
declare(strict_types=1);
namespace Viserio\Container\Tests;

use StdClass;
use Viserio\Container\Container;
use Viserio\Container\Tests\Fixture\ContainerConcreteFixture;
use Viserio\Container\Tests\Fixture\ContainerContractFixtureInterface;
use Viserio\Container\Tests\Fixture\ContainerDependentFixture;
use Viserio\Container\Tests\Fixture\ContainerNestedDependentFixture;
use Viserio\Container\Tests\Fixture\ContainerImplementationFixture;
use Viserio\Container\Tests\Fixture\ContainerDefaultValueFixture;
use Viserio\Container\Tests\Fixture\ContainerTestInterfaceFixture;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
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
        $stub = new ContainerDependentFixture($mock = $this->createMock(ContainerContractFixtureInterface::class));
        $resolved = $container->make(ContainerNestedDependentFixture::class, [$stub]);

        $this->assertInstanceOf(ContainerNestedDependentFixture::class, $resolved);
        $this->assertEquals($mock, $resolved->inner->impl);
    }

    public function testAbstractToConcreteResolution()
    {
        $container = new Container;
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $class = $container->make(ContainerDependentFixture::class);

        $this->assertInstanceOf(ContainerImplementationFixture::class, $class->impl);
    }

    public function testNestedDependencyResolution()
    {
        $container = new Container;
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
    }

    public function testAliases()
    {
        $container = new Container;
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
        $this->assertSame($result, $container->make('foo'));
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
        $container = $this->container;
        $container->bind('foo', function () {
            $obj = new StdClass();
            $obj->foo = 'bar';

            return $obj;
        });
        $obj = new StdClass();
        $obj->foo = 'foo';
        $container->singleton('foo', $obj);
        $container->extend('foo', function ($obj, $container) {
            $obj->bar = 'baz';

            return $obj;
        });
        $container->extend('foo', function ($obj, $container) {
            $obj->baz = 'foo';

            return $obj;
        });

        $this->assertEquals('foo', $container->make('foo')->foo);
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
        $container = new Container;
        $container->instance('object', new StdClass);
        unset($container['object']);

        $this->assertFalse($container->has('object'));

        $container->instance('object', new StdClass);
        $container->forget('object');

        $this->assertFalse($container->has('object'));
    }

    public function testBoundInstanceAndAliasCheckViaArrayAccess()
    {
        $container = new Container();
        $container->instance('object', new StdClass);
        $container->alias('object', 'alias');

        $this->assertTrue(isset($container['object']));
        $this->assertTrue(isset($container['alias']));
    }

    public function testCircularReferenceCheck()
    {
        // Since the dependency is ( D -> F -> E -> D ), the exception
        // message should state that the issue starts in class D
        $this->setExpectedException('Viserio\Container\CircularReferenceException', 'Circular reference found while resolving [ContainerCircularReferenceStubD].');
        $container = $this->container;
        $parameters = [];
        $container->make('ContainerCircularReferenceStubD', $parameters);
    }

    public function testCircularReferenceCheckDetectCycleStartLocation()
    {
        // Since the dependency is ( A -> B -> C -> B ), the exception
        // message should state that the issue starts in class B
        $this->setExpectedException('Viserio\Container\CircularReferenceException', 'Circular reference found while resolving [ContainerCircularReferenceStubB].');
        $container = $this->container;
        $parameters = [];
        $container->make('ContainerCircularReferenceStubA', $parameters);
    }

    /**
     * Methods should using contextual binding
     */
    public function testContextualBindingOnMethods()
    {
        $container = new Container();
        $container->when(ContainerTestInterfaceFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationFixture::class);

         // Works if using constructor
        $constructor = $container->make(ContainerTestInterfaceFixture::class);
        $result = $constructor->getStub();

        $this->assertInstanceOf(ContainerImplementationFixture::class, $result);

         // Doesn't work if using methods
        $result = $container->call(ContainerTestInterfaceFixture::class . '::go');

        $this->assertInstanceOf(ContainerImplementationFixture::class, $result);
    }

    public function testExtendedBindingsKeptTypes()
    {
        $container = new Container();

        $container->singleton('foo', function() {
            return (object) ['name' => 'narrowspark'];
        });

        $container->extend('foo', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $container->bind('bar', function() {
            return (object) ['name' => 'narrowspark'];
        });

        $container->extend('bar', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $this->assertSame($container->make('foo'), $container->make('foo'));
        $this->assertNotSame($container->make('bar'), $container->make('bar'));
    }
}
