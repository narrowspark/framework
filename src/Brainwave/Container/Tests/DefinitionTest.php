<?php

namespace Brainwave\Container\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.8-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Container\Container;
use Brainwave\Container\Definition;

/**
 * ContainerTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Qux');
        $this->assertAttributeInstanceOf(
            'Brainwave\\Container\\Container',
            'container',
            $definition,
            'The passed container name should be assigned to the $container property.'
        );
        $this->assertAttributeEquals(
            'Brainwave\\Test\\Container\\Qux',
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Qux');
        $instance = $definition();
        $this->assertInstanceOf(
            'Brainwave\\Test\\Container\\Qux',
            $instance,
            'Invoking the Definition class should return an instance of the class $class.'
        );
    }

    /**
     * Tests invoking a class with defined args.
     */
    public function testInvokeWithArgs()
    {
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Foo');
        $definition->withArgument('Brainwave\\Test\\Container\\Bar')->withArgument('Brainwave\\Test\\Container\\Baz');
        $instance = $definition();
        $this->assertInstanceOf(
            'Brainwave\\Test\\Container\\Foo',
            $instance,
            'Invoking a Definition should return an instance of the class defined in the $class property.'
        );
        $this->assertAttributeInstanceOf(
            'Brainwave\\Test\\Container\\Bar',
            'bar',
            $instance,
            'Invoking a Definition with arguments assigned should pass those args to the method.'
        );
        $this->assertAttributeInstanceOf(
            'Brainwave\\Test\\Container\\Baz',
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
        $this->container->bind('Brainwave\\Test\\Container\\CorgeInterface')->withArgument($int);
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Corge');
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Corge');
        $definition->withArgument(1);
        $instance = $definition();
        $this->assertInstanceOf(
            'Brainwave\\Test\\Container\\Corge',
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Qux');
        $definition->withMethodCall('setBar', ['Brainwave\\Test\\Container\\Bar']);
        $instance = $definition();
        $this->assertInstanceOf(
            'Brainwave\\Test\\Container\\Qux',
            $instance,
            'Invoking a Definition should return an instance of the class defined in the $class property.'
        );
        $this->assertAttributeInstanceOf(
            'Brainwave\\Test\\Container\\Bar',
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
        $this->container->bind('Brainwave\\Test\\Container\\CorgeInterface')
            ->withMethodCall('setInt', [$int]);
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Corge');
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Foo');
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Foo');
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Foo');
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Foo');
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
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Qux');
        $definition->withMethodCall('setBar', ['Brainwave\\Test\\Container\\Bar']);
        $methods = $this->readAttribute($definition, 'methods');
        $this->assertArrayHasKey(
            'setBar',
            $methods,
            'Calling withMethod should set the defined method into the methods array.'
        );
    }

    public function testCallMethod()
    {
        $definition = new Definition($this->container, 'Brainwave\\Test\\Container\\Corge');
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
