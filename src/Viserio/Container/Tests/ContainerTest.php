<?php
<<<<<<< HEAD
namespace Viserio\Container\Tests;
=======
namespace Viserio\Container\Test;
>>>>>>> develop

use Viserio\Container\Container;

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
            $service = new \stdClass();
            $service->id = $id;

            $this->services[$id] = $service;
            $this->container->set($id, $service);
        }
    }

    // public function testClosureResolution()
    // {
    //     $container = $this->container;
    //     $container->bind('name', function () { return 'Viserio'; });
    //     $this->assertEquals('Viserio', $container->make('name'));
    // }

    // public function testSharedClosureResolution()
    // {
    //     $container = $this->container;
    //     $class = new \stdClass();
    //     $container->singleton('class', function () use ($class) { return $class; });
    //     $this->assertSame($class, $container->make('class'));
    // }

    // public function testAutoConcreteResolution()
    // {
    //     $container = $this->container;
    //     $this->assertInstanceOf('ContainerConcreteStub', $container->make('ContainerConcreteStub'));
    // }

    // public function testSharedConcreteResolution()
    // {
    //     $container = $this->container;
    //     $container->singleton('ContainerConcreteStub');
    //     $bindings = $container->getBindings();
    //     $var1 = $container->make('ContainerConcreteStub');
    //     $var2 = $container->make('ContainerConcreteStub');
    //     $this->assertSame($var1, $var2);
    // }

    // public function testContainerIsPassedToResolvers()
    // {
    //     $container = $this->container;
    //     $container->bind('something', function ($c) { return $c; });
    //     $c = $container->make('something');
    //     $this->assertSame($c, $container);
    // }

    // public function testArrayAccess()
    // {
    //     $container = $this->container;
    //     $container['something'] = function () { return 'foo'; };
    //     $this->assertTrue(isset($container['something']));
    //     $this->assertEquals('foo', $container['something']);
    //     unset($container['something']);
    //     $this->assertFalse(isset($container['something']));
    // }

    // public function testAliases()
    // {
    //     $container = $this->container;
    //     $container['foo'] = 'bar';
    //     $container->alias('foo', 'baz');
    //     $this->assertEquals('bar', $container->make('foo'));
    //     $this->assertEquals('bar', $container->make('baz'));
    //     $container->bind(['bam' => 'boom'], function () { return 'pow'; });
    //     $this->assertEquals('pow', $container->make('bam'));
    //     $this->assertEquals('pow', $container->make('boom'));
    //     $container->singleton(['zoom' => 'zing'], 'wow');
    //     $this->assertEquals('wow', $container->make('zoom'));
    //     $this->assertEquals('wow', $container->make('zing'));
    // }

    // public function testBindingsCanBeOverridden()
    // {
    //     $container = $this->container;
    //     $container['foo'] = 'bar';
    //     $foo = $container['foo'];
    //     $container['foo'] = 'baz';
    //     $this->assertEquals('baz', $container['foo']);
    // }

    // public function testExtendedBindings()
    // {
    //     $container = $this->container;
    //     $container['foo'] = 'foo';
    //     $container->extend('foo', function ($old, $container) {
    //         return $old.'bar';
    //     });

    //     $this->assertEquals('foobar', $container->make('foo'));
    //     $container = $this->container;
    //     $container['foo'] = function () {
    //         return (object) ['name' => 'narrowspark'];
    //     };

    //     $container->extend('foo', function ($old, $container) {
    //         $old->age = 26;

    //         return $old;
    //     });

    //     $result = $container->make('foo');
    //     $this->assertEquals('narrowspark', $result->name);
    //     $this->assertEquals(26, $result->age);
    //     $this->assertSame($result, $container->make('foo'));
    // }

    // public function testMultipleExtends()
    // {
    //     $container = $this->container;
    //     $container['foo'] = 'foo';

    //     $container->extend('foo', function ($old, $container) {
    //         return $old.'bar';
    //     });

    //     $container->extend('foo', function ($old, $container) {
    //         return $old.'baz';
    //     });

    //     $this->assertEquals('foobarbaz', $container->make('foo'));
    // }

    // public function testExtendInstancesArePreserved()
    // {
    //     $container = $this->container;
    //     $container->bind('foo', function () { $obj = new \StdClass(); $obj->foo = 'bar';
    //         return $obj;
    //     });
    //     $obj = new \StdClass();
    //     $obj->foo = 'foo';
    //     $container->singleton('foo', $obj);
    //     $container->extend('foo', function ($obj, $container) { $obj->bar = 'baz';
    //         return $obj;
    //     });

    //     $container->extend('foo', function ($obj, $container) { $obj->baz = 'foo';
    //         return $obj;
    //     });
    //     $this->assertEquals('foo', $container->make('foo')->foo);
    // }

    // public function testExtendCanBeCalledBeforeBind()
    // {
    //     $container = $this->container;
    //     $container->extend('foo', function ($old, $container) {
    //         return $old.'bar';
    //     });
    //     $container['foo'] = 'foo';
    //     $this->assertEquals('foobar', $container->make('foo'));
    // }

    // public function testParametersCanBePassedThroughToClosure()
    // {
    //     $container = $this->container;
    //     $container->bind('foo', function ($c, $parameters) {
    //         return $parameters;
    //     });
    //     $this->assertEquals([1, 2, 3], $container->make('foo', [1, 2, 3]));
    // }

    // public function testCallWithDependencies()
    // {
    //     $container = $this->container;
    //     $result = $container->call(function (\StdClass $foo, $bar = []) {
    //         return func_get_args();
    //     });
    //     $this->assertInstanceOf('stdClass', $result[0]);
    //     $this->assertEquals([], $result[1]);
    //     $result = $container->call(function (\StdClass $foo, $bar = []) {
    //         return func_get_args();
    //     }, ['bar' => 'viserio']);
    //     $this->assertInstanceOf('stdClass', $result[0]);
    //     $this->assertEquals('viserio', $result[1]);
    //     /*
    //      * Wrap a function...
    //      */
    //     $result = $container->wrap(function (\StdClass $foo, $bar = []) {
    //         return func_get_args();
    //     }, ['bar' => 'viserio']);
    //     $this->assertInstanceOf('Closure', $result);
    //     $result = $result();
    //     $this->assertInstanceOf('stdClass', $result[0]);
    //     $this->assertEquals('viserio', $result[1]);
    // }

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
        $container = new Container;
        $container->when("Viserio\Container\Tests\Fixture\ContainerTestInterfaceStub")
            ->needs("Viserio\Container\Tests\Fixture\ContainerContractStub")
            ->give("Viserio\Container\Tests\Fixture\ContainerImplementationStub");

         // Works if using constructor
        $constructor = $container->make('Viserio\Container\Tests\Fixture\ContainerTestInterfaceStub');
        $result = $constructor->getStub();
        $this->assertInstanceOf("Viserio\Container\Tests\Fixture\ContainerImplementationStub", $result);

         // Doesn't work if using methods
        $result = $container->call('Viserio\Container\Tests\Fixture\ContainerTestInterfaceStub@go');
        $this->assertInstanceOf("Viserio\Container\Tests\Fixture\ContainerImplementationStub", $result);
    }
}
