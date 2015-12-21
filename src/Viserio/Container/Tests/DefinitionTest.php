<?php
namespace Viserio\Container\Test;

use Viserio\Container\Container;
use Viserio\Container\Definition;

/**
 * ContainerTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class DefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Setup procedure which runs before each test.
     */
    public function setUp()
    {
        $this->container = new Container();
    }

    /**
     * Tests that the class properties are set correctly.
     */
    public function testConstruct()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Qux');
        $this->assertAttributeInstanceOf(
            'Viserio\\Container\\Container',
            'container',
            $definition,
            'The passed container name should be assigned to the $container property.'
        );
        $this->assertAttributeEquals(
            'Viserio\\Test\\Container\\Qux',
            'class',
            $definition,
            'The passed class name should be assigned to the $class property.'
        );
    }

    /**
     * Tests invoking a class with no args or method calls.
     */
    public function testInvokeNoArgsOrMethodCalls()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Qux');
        $instance = $definition();
        $this->assertInstanceOf(
            'Viserio\\Test\\Container\\Qux',
            $instance,
            'Invoking the Definition class should return an instance of the class $class.'
        );
    }

    /**
     * Tests invoking a class with defined args.
     */
    public function testInvokeWithArgs()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Foo');
        $definition->withArgument('Viserio\\Test\\Container\\Bar')->withArgument('Viserio\\Test\\Container\\Baz');
        $instance = $definition();
        $this->assertInstanceOf(
            'Viserio\\Test\\Container\\Foo',
            $instance,
            'Invoking a Definition should return an instance of the class defined in the $class property.'
        );
        $this->assertAttributeInstanceOf(
            'Viserio\\Test\\Container\\Bar',
            'bar',
            $instance,
            'Invoking a Definition with arguments assigned should pass those args to the method.'
        );
        $this->assertAttributeInstanceOf(
            'Viserio\\Test\\Container\\Baz',
            'baz',
            $instance,
            'Invoking a Definition with arguments assigned should pass those args to the method.'
        );
    }

    /**
     * Tests invoking a class with inherited args.
     */
    public function testInvokeWithInheritedArgs()
    {
        $int = rand(1, 5000);
        $this->container->bind('Viserio\\Test\\Container\\CorgeInterface')->withArgument($int);
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Corge');
        $instance = $definition();
        $this->assertAttributeEquals(
            $int,
            'int',
            $instance,
            'Invoking a Definition with inherited arguments should pass those args to the constructor.'
        );
    }

    /**
     * Tests invoking a class with an integer as an args.
     */
    public function testInvokeWithIntegerAsArg()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Corge');
        $definition->withArgument(1);
        $instance = $definition();
        $this->assertInstanceOf(
            'Viserio\\Test\\Container\\Corge',
            $instance,
            'Invoking a Definition should return an instance of the class defined in the $class property.'
        );
        $this->assertAttributeEquals(
            1,
            'int',
            $instance,
            'Invoking a Definition with arguments assigned should pass those args to the method.'
        );
    }

    /**
     * Tests invoking a class with a defined method call.
     */
    public function testInvokeWithMethodCall()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Qux');
        $definition->withMethodCall('setBar', ['Viserio\\Test\\Container\\Bar']);
        $instance = $definition();
        $this->assertInstanceOf(
            'Viserio\\Test\\Container\\Qux',
            $instance,
            'Invoking a Definition should return an instance of the class defined in the $class property.'
        );
        $this->assertAttributeInstanceOf(
            'Viserio\\Test\\Container\\Bar',
            'bar',
            $instance,
            'Invoking a Definition with a defined method call pass the defined args to the method.'
        );
    }

    /**
     * Tests invoking a class with inherited method call.
     */
    public function testInvokeWithInheritedMethodCall()
    {
        $int = rand(1, 5000);
        $this->container->bind('Viserio\\Test\\Container\\CorgeInterface')
            ->withMethodCall('setInt', [$int]);
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Corge');
        $instance = $definition();
        $this->assertAttributeEquals(
            $int,
            'int',
            $instance,
            'Invoking a Definition with inherited method calls should pass those call those methods.'
        );
    }

    /**
     * Tests adding an argument to a Defintion.
     */
    public function testWithArgument()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Foo');
        $definition->withArgument('foo');
        $this->assertAttributeContains(
            'foo',
            'arguments',
            $definition,
            'An added argument should be added to the arguments array.'
        );
    }

    /**
     * Tests adding an argument to a Defintion.
     */
    public function testAddIntegerArg()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Foo');
        $definition->withArgument(1);
        $args = $this->readAttribute($definition, 'arguments');
        $this->assertEquals(
            $args[0],
            1,
            'An added argument should be added to the arguments array, regardless of type'
        );
    }

    /**
     * Tests adding multiple arguments to a Defintion.
     */
    public function testWithArguments()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Foo');
        $definition->withArguments(['foo', 'bar']);
        $this->assertAttributeEquals(
            ['foo', 'bar'],
            'arguments',
            $definition,
            'Added arguments should be added to the arguments array.'
        );
    }

    /**
     * Tests removing arguments from a Defintion.
     */
    public function testCleanArgs()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Foo');
        $definition->withArguments(['foo', 'bar']);
        $definition->cleanArgument();
        $this->assertAttributeEquals(
            [],
            'arguments',
            $definition,
            'All arguments should be removed from the arguments array.'
        );
    }

    public function testWithMethod()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Qux');
        $definition->withMethodCall('setBar', ['Viserio\\Test\\Container\\Bar']);
        $methods = $this->readAttribute($definition, 'methods');
        $this->assertArrayHasKey(
            'setBar',
            $methods,
            'Calling withMethod should set the defined method into the methods array.'
        );
    }

    public function testCallMethod()
    {
        $definition = new Definition($this->container, 'Viserio\\Test\\Container\\Corge');
        $definition->withMethodCall('setInt', [1]);
        $reflection = new \ReflectionMethod($definition, 'callMethods');
        $reflection->setAccessible(true);
        $object = new Corge();
        $objectWithMethodsCalled = $reflection->invoke($definition, $object);
        $this->assertAttributeEquals(
            1,
            'int',
            $objectWithMethodsCalled,
            'Running callMethod on a given object should call the method and pass the args.'
        );
    }
}

interface BarInterface
{
}

class Bar implements BarInterface
{
    public $qux;

    public function __construct(Qux $qux)
    {
        $this->qux = $qux;
    }
}

interface BazInterface
{
}

class Baz implements BazInterface
{
    public function noDependencies()
    {
        return true;
    }

    public function noTypeHint($arg = 'baz')
    {
        return $arg;
    }

    public function noTypeHintOrDefaultValue($arg)
    {
        return $arg;
    }
}

interface CorgeInterface
{
    public function __construct($int = null);

    public function setInt($int);
}

class Corge implements CorgeInterface
{
    public $int;

    public function __construct($int = null)
    {
        $this->int = $int;
    }

    public function setInt($int)
    {
        $this->int = $int;
    }
}

class Qux
{
    public $bar;

    public function setBar(BarInterface $bar)
    {
        $this->bar = $bar;
    }
}

class Foo
{
    public $bar;
    public $baz;

    public function __construct(BarInterface $bar, BazInterface $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }
}
