<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use Mouf\Picotainer\Picotainer;
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

/**
 * @internal
 */
final class ContainerTest extends MockeryTestCase
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();

        $this->services = ['test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null];

        foreach (\array_keys($this->services) as $id) {
            $service     = new stdClass();
            $service->id = $id;

            $this->services[$id] = $service;
            $this->container->bind($id, $service);
        }
    }

    public function testClosureResolution(): void
    {
        $container = $this->container;
        $container->bind('name', function () {
            return 'Narrowspark';
        });

        $this->assertEquals('Narrowspark', $container->resolve('name'));
    }

    public function testBindIfDoesntRegisterIfServiceAlreadyRegistered(): void
    {
        $container = new Container();

        $container->bind('name', function () {
            return 'Narrowspark';
        });

        $container->bindIf('name', function () {
            return 'Viserio';
        });

        $this->assertEquals('Narrowspark', $container->resolve('name'));
    }

    public function testSharedClosureResolution(): void
    {
        $container = $this->container;
        $class     = new stdClass();

        $container->singleton('class', function () use ($class) {
            return $class;
        });

        $this->assertSame($class, $container->resolve('class'));
    }

    public function testAutoConcreteResolution(): void
    {
        $container = $this->container;

        $this->assertInstanceOf(ContainerConcreteFixture::class, $container->resolve(ContainerConcreteFixture::class));
    }

    public function testResolveMethod(): void
    {
        $container = new Container();

        $this->assertEquals('Hello', $container->resolve(FactoryClass::class . '::create'));
    }

    public function testResolveMethodFromString(): void
    {
        $container = new Container();

        $this->assertEquals('Hello', $container->resolveMethod(FactoryClass::class . '::staticCreate'));
        $this->assertEquals('Hello', $container->resolveMethod(FactoryClass::class . '::staticCreateWitArg', ['name' => 'Hello']));
    }

    public function testSharedConcreteResolution(): void
    {
        $container = $this->container;
        $container->singleton(ContainerConcreteFixture::class);

        $var1 = $container->resolve(ContainerConcreteFixture::class);
        $var2 = $container->resolve(ContainerConcreteFixture::class);

        $this->assertSame($var1, $var2);
    }

    public function testParametersCanOverrideDependencies(): void
    {
        $container = new Container();
        $mock      = $this->mock(ContainerContractFixtureInterface::class);
        $stub      = new ContainerDependentFixture($mock);
        $resolved  = $container->resolve(ContainerNestedDependentFixture::class, [$stub]);

        $this->assertInstanceOf(ContainerNestedDependentFixture::class, $resolved);
        $this->assertEquals($mock, $resolved->inner->impl);
    }

    public function testResolveNonBoundWithClosure(): void
    {
        $container = new Container();

        $class = $container->resolveNonBound(function ($container) {
            return $container;
        });

        $this->assertInstanceOf(Container::class, $class);
    }

    public function testAbstractToConcreteResolution(): void
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $class = $container->resolve(ContainerDependentFixture::class);

        $this->assertInstanceOf(ContainerImplementationFixture::class, $class->impl);
    }

    public function testNestedDependencyResolution(): void
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $class = $container->resolve(ContainerNestedDependentFixture::class);

        $this->assertInstanceOf(ContainerDependentFixture::class, $class->inner);
        $this->assertInstanceOf(ContainerImplementationFixture::class, $class->inner->impl);
    }

    public function testContainerIsPassedToResolvers(): void
    {
        $container = $this->container;
        $container->bind('something', function ($c) {
            return $c;
        });

        $c = $container->resolve('something');

        $this->assertSame($c, $container);
    }

    public function testArrayAccess(): void
    {
        $container              = $this->container;
        $container['something'] = function () {
            return 'foo';
        };

        $this->assertTrue(isset($container['something']));
        $this->assertEquals('foo', $container['something']);

        unset($container['something']);

        $this->assertFalse(isset($container['something']));

        $container['foo'] = 'foo';
        $result           = $container->resolve('foo');

        $this->assertSame($result, $container->resolve('foo'));
    }

    public function testAliases(): void
    {
        $container        = new Container();
        $container['foo'] = 'bar';
        $container->alias('foo', 'baz');
        $container->alias('baz', 'bat');

        $this->assertSame('bar', $container->resolve('foo'));
        $this->assertSame('bar', $container->resolve('baz'));
        $this->assertSame('bar', $container->resolve('bat'));
    }

    public function testBindingsCanBeOverridden(): void
    {
        $container        = $this->container;
        $container['foo'] = 'bar';
        $foo              = $container['foo'];

        $this->assertSame('bar', $foo);

        $container['foo'] = 'baz';

        $this->assertSame('baz', $container['foo']);
    }

    public function testExtendedBindings(): void
    {
        $container        = $this->container;
        $container['foo'] = 'foo';
        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        $this->assertSame('foobar', $container->resolve('foo'));

        $container        = $this->container;
        $container['foo'] = function () {
            return (object) ['name' => 'narrowspark'];
        };

        $container->extend('foo', function ($old, $container) {
            $old->oldName = 'viserio';

            return $old;
        });

        $result = $container->resolve('foo');

        $this->assertSame('narrowspark', $result->name);
        $this->assertSame('viserio', $result->oldName);
    }

    public function testMultipleExtends(): void
    {
        $container        = $this->container;
        $container['foo'] = 'foo';

        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });

        $container->extend('foo', function ($old, $container) {
            return $old . 'baz';
        });

        $this->assertEquals('foobarbaz', $container->resolve('foo'));
    }

    public function testExtendInstancesArePreserved(): void
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

        $this->assertEquals('foo', $container->resolve('foo')->foo);
        $this->assertEquals('baz', $container->resolve('foo')->bar);
        $this->assertEquals('foo', $container->resolve('foo')->baz);
    }

    public function testExtendCanBeCalledBeforeBind(): void
    {
        $container = $this->container;
        $container->extend('foo', function ($old, $container) {
            return $old . 'bar';
        });
        $container['foo'] = 'foo';

        $this->assertEquals('foobar', $container->resolve('foo'));
    }

    public function testParametersCanBePassedThroughToClosure(): void
    {
        $container = $this->container;
        $container->bind('foo', function ($container, $a, $b, $c) {
            return [$a, $b, $c];
        });

        $this->assertEquals([1, 2, 3], $container->resolve('foo', [1, 2, 3]));
    }

    public function testResolutionOfDefaultParameters(): void
    {
        $container = new Container();
        $instance  = $container->resolve(ContainerDefaultValueFixture::class);

        $this->assertInstanceOf(ContainerConcreteFixture::class, $instance->stub);
        $this->assertEquals('narrowspark', $instance->default);
    }

    public function testUnsetRemoveBoundInstances(): void
    {
        $container = new Container();
        $container->instance('object', new stdClass());
        unset($container['object']);

        $this->assertFalse($container->has('object'));

        $container->instance('object', new stdClass());
        $container->forget('object');

        $this->assertFalse($container->has('object'));
    }

    public function testHasToThrowExceptionOnNoStringType(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\ContainerException::class);
        $this->expectExceptionMessage('The name parameter must be of type string, [stdClass] given.');

        $container = new Container();

        $this->assertFalse($container->has(new stdClass()));
    }

    public function testGetToThrowExceptionOnNoStringType(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\ContainerException::class);
        $this->expectExceptionMessage('The id parameter must be of type string, [stdClass] given.');

        $container = new Container();

        $this->assertFalse($container->get(new stdClass()));
    }

    public function testGetToThrowExceptionOnNotFoundId(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Abstract [test] is not being managed by the container.');

        $container = new Container();

        $this->assertFalse($container->get('test'));
    }

    public function testBoundInstanceAndAliasCheckViaArrayAccess(): void
    {
        $container = new Container();
        $container->instance('object', new stdClass());
        $container->alias('object', 'alias');

        $this->assertTrue(isset($container['object']));
        $this->assertTrue(isset($container['alias']));
    }

    public function testCircularReferenceCheck(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\CyclicDependencyException::class);
        $this->expectExceptionMessage('Circular reference found while resolving [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerCircularReferenceStubD].');

        // Since the dependency is ( D -> F -> E -> D ), the exception
        // message should state that the issue starts in class D
        $container = $this->container;
        $container->resolve(ContainerCircularReferenceStubD::class);
    }

    public function testCircularReferenceCheckDetectCycleStartLocation(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\CyclicDependencyException::class);
        $this->expectExceptionMessage('Circular reference found while resolving [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerCircularReferenceStubB].');

        // Since the dependency is ( A -> B -> C -> B ), the exception
        // message should state that the issue starts in class B
        $container = $this->container;
        $container->resolve(ContainerCircularReferenceStubA::class);
    }

    public function testContainerCanInjectSimpleVariable(): void
    {
        $container = new Container();
        $container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $container->resolve(ContainerInjectVariableFixture::class);

        $this->assertEquals(100, $instance->something);

        $container = new Container();
        $container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')->give(function ($container) {
                return $container->resolve(ContainerConcreteFixture::class);
            });

        $instance = $container->resolve(ContainerInjectVariableFixture::class);

        $this->assertInstanceOf(ContainerConcreteFixture::class, $instance->something);
    }

    public function testContainerCanInjectSimpleVariableToBinding(): void
    {
        $container = new Container();
        $container->bind(ContainerInjectVariableFixture::class);

        $container->when(ContainerInjectVariableFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $container->resolve(ContainerInjectVariableFixture::class);

        $this->assertEquals(100, $instance->something);
    }

    public function testContainerWhenNeedsGiveToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Parameter [something] cannot be injected in [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerInjectVariableFixture].');

        $container = new Container();

        $container->when(ContainerInjectVariableFixture::class . '::set')
            ->needs(ContainerConcreteFixture::class)
            ->needs('$something')
            ->give(100);
        $instance = $container->resolve(ContainerInjectVariableFixture::class);

        $this->assertEquals(100, $instance->something);
    }

    public function testContainerCantInjectObjectIsNotResolvable(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\ContainerTestNotResolvable] is not resolvable.');

        $container = new Container();

        $container->when('Viserio\Component\Container\Tests\ContainerTestNotResolvable')
            ->needs(ContainerConcreteFixture::class)
            ->give(100);
        $instance = $container->resolve(ContainerInjectVariableFixture::class);

        $this->assertEquals(100, $instance->something);
    }

    public function testContainerCantInjectObjectWithoutConstructor(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\Fixture\\ContainerTestNoConstructor] must have a constructor.');

        $container = new Container();

        $container->when(ContainerTestNoConstructor::class)
            ->needs(ContainerConcreteFixture::class)
            ->give(100);
        $instance = $container->resolve(ContainerInjectVariableFixture::class);

        $this->assertEquals(100, $instance->something);
    }

    public function testContainerCanInjectDifferentImplementationsDependingOnContext(): void
    {
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationFixture::class);

        $container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationTwoFixture::class);

        $one = $container->resolve(ContainerTestContextInjectOneFixture::class);
        $two = $container->resolve(ContainerTestContextInjectTwoFixture::class);

        $this->assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        $this->assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);

        // Test With Closures
        $container = new Container();
        $container->bind(ContainerContractFixtureInterface::class, ContainerImplementationFixture::class);
        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationFixture::class);
        $container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(function ($container) {
                return $container->resolve(ContainerImplementationTwoFixture::class);
            });

        $one = $container->resolve(ContainerTestContextInjectOneFixture::class);
        $two = $container->resolve(ContainerTestContextInjectTwoFixture::class);

        $this->assertInstanceOf(ContainerImplementationFixture::class, $one->impl);
        $this->assertInstanceOf(ContainerImplementationTwoFixture::class, $two->impl);
    }

    public function testInternalClassWithDefaultParameters(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\BindingResolutionException::class);
        $this->expectExceptionMessage('Unresolvable dependency resolving [Parameter #0 [ <required> $first ]] in [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerMixedPrimitiveFixture]');

        $container = new Container();
        $container->resolve(ContainerMixedPrimitiveFixture::class);
    }

    public function testUnableToReflectClass(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\BindingResolutionException::class);
        $this->expectExceptionMessage('Unable to reflect on the class [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerPrivateConstructor], does the class exist and is it properly autoloaded?');

        $container = new Container();
        $container->resolve(ContainerPrivateConstructor::class);
    }

    public function testBindingResolutionExceptionMessage(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\BindingResolutionException::class);
        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\Fixture\\ContainerContractFixtureInterface] is not resolvable. Build stack : []');

        $container = new Container();
        $container->resolve(ContainerContractFixtureInterface::class);
    }

    public function testBindingResolutionExceptionMessageIncludesBuildStack(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\BindingResolutionException::class);
        $this->expectExceptionMessage('[Viserio\\Component\\Container\\Tests\\Fixture\\ContainerContractFixtureInterface] is not resolvable. Build stack : [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerTestContextInjectOneFixture]');

        $container = new Container();
        $container->resolve(ContainerTestContextInjectOneFixture::class);
    }

    public function testDelegateContainer(): void
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
        $this->assertFalse($container->hasInDelegate('instance3'));
    }

    public function testExtendedBindingsKeptTypes(): void
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

        $this->assertSame($container->resolve('foo'), $container->resolve('foo'));
        $this->assertNotSame($container->resolve('bar'), $container->resolve('bar'));
    }

    public function testExtendIsLazyInitialized(): void
    {
        ContainerLazyExtendFixture::$initialized = false;

        $container = new Container();
        $container->bind(ContainerLazyExtendFixture::class);
        $container->extend(ContainerLazyExtendFixture::class, function ($obj, $container) {
            $obj->init();

            return $obj;
        });

        $this->assertFalse(ContainerLazyExtendFixture::$initialized);

        $container->resolve(ContainerLazyExtendFixture::class);

        $this->assertTrue(ContainerLazyExtendFixture::$initialized);
    }

    public function testContextualBindingWorksForExistingInstancedBindings(): void
    {
        $container = new Container();

        $container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerImplementationTwoFixture::class);

        $this->assertInstanceOf(
            ContainerImplementationTwoFixture::class,
            $container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksForNewlyInstancedBindings(): void
    {
        $container = new Container();

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());

        $this->assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnExistingAliasedInstances(): void
    {
        $container = new Container();

        $container->instance('stub', new ContainerImplementationFixture());
        $container->alias('stub', ContainerContractFixtureInterface::class);

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $this->assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnNewAliasedInstances(): void
    {
        $container = new Container();

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $container->instance('stub', new ContainerImplementationFixture());
        $container->alias('stub', ContainerContractFixtureInterface::class);

        $this->assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingWorksOnNewAliasedBindings(): void
    {
        $container = new Container();

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectTwoFixture::class);

        $container->bind('stub', ContainerImplementationFixture::class);
        $container->alias('stub', ContainerContractFixtureInterface::class);

        $this->assertInstanceOf(
            ContainerTestContextInjectTwoFixture::class,
            $container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextualBindingDoesntOverrideNonContextualResolution(): void
    {
        $container = new Container();

        $container->instance('stub', new ContainerImplementationFixture());
        $container->alias('stub', ContainerContractFixtureInterface::class);

        $container->when(ContainerTestContextInjectTwoFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give(ContainerTestContextInjectInstantiationsFixture::class);

        $this->assertInstanceOf(
            ContainerTestContextInjectInstantiationsFixture::class,
            $container->resolve(ContainerTestContextInjectTwoFixture::class)->impl
        );

        $this->assertInstanceOf(
            ContainerImplementationFixture::class,
            $container->resolve(ContainerTestContextInjectOneFixture::class)->impl
        );
    }

    public function testContextuallyBoundInstancesAreNotUnnecessarilyRecreated(): void
    {
        ContainerTestContextInjectInstantiationsFixture::$instantiations = 0;

        $container = new Container();

        $container->instance(ContainerContractFixtureInterface::class, new ContainerImplementationFixture());
        $container->instance('ContainerTestContextInjectInstantiationsFixture', new ContainerTestContextInjectInstantiationsFixture());

        $this->assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);

        $container->when(ContainerTestContextInjectOneFixture::class)
            ->needs(ContainerContractFixtureInterface::class)
            ->give('ContainerTestContextInjectInstantiationsFixture');

        $container->resolve(ContainerTestContextInjectOneFixture::class);
        $container->resolve(ContainerTestContextInjectOneFixture::class);
        $container->resolve(ContainerTestContextInjectOneFixture::class);
        $container->resolve(ContainerTestContextInjectOneFixture::class);

        $this->assertEquals(1, ContainerTestContextInjectInstantiationsFixture::$instantiations);
    }

    public function testContextualBindingNotWorksOnBoundAlias(): void
    {
        $this->expectException(\Viserio\Component\Contract\Container\Exception\UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Parameter [stub] cannot be injected in [Viserio\\Component\\Container\\Tests\\Fixture\\ContainerTestContextInjectOneFixture].');

        $container = new Container();

        $container->alias(ContainerContractFixtureInterface::class, 'stub');
        $container->bind('stub', ContainerImplementationFixture::class);

        $container->when(ContainerTestContextInjectOneFixture::class)->needs('stub')->give(ContainerImplementationTwoFixture::class);

        $container->get(ContainerTestContextInjectOneFixture::class);
    }

    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        $this->assertEquals('value', $container['param']);
        $this->assertInstanceOf(ServiceFixture::class, $container['service']);
    }

    public function testProviderWithRegisterMethod(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider(), [
            'anotherParameter' => 'anotherValue',
        ]);

        $this->assertEquals('value', $container->get('param'));
        $this->assertEquals('anotherValue', $container->get('anotherParameter'));
        $this->assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testExtendingValue(): void
    {
        $container = new Container();
        $container->instance('previous', 'foo');
        $container->register(new SimpleFixtureServiceProvider());

        $this->assertEquals('foofoo', $container->get('previous'));
    }

    public function testExtendingNothing(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        $this->assertSame('', $container->get('previous'));
    }
}
