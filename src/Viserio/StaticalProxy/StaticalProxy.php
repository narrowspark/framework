<?php
namespace Viserio\StaticalProxy;

use BadMethodCallException;
use Interop\Container\ContainerInterface;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;

abstract class StaticalProxy
{
    /**
     * @var ContainerInterface The Container that provides the Proxy Subjects.
     */
    protected static $container;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance = [];

    /**
     * Sets the Container that will be used to retrieve the Proxy Subject.
     *
     * @param ContainerInterface $container The Container that provides the real Proxy Subject
     */
    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    /**
     * Retrieves the instance of the Proxy Subject from the Container that the Static Proxy is associated with
     *
     * @return mixed
     */
    public static function getInstance()
    {
        return static::$container->get(static::getInstanceIdentifier());
    }

    /**
     * Retrieves the Instance Identifier that is used to retrieve the Proxy Subject from the Container
     *
     * @throws \BadMethodCallException if the method has not been implemented by a subclass
     *
     * @return NoType
     */
    public static function getInstanceIdentifier()
    {
        throw new BadMethodCallException('The ' . __METHOD__ . ' method must be implemented by a subclass.');
    }

    /**
     * Hotswap the underlying instance behind the facade.
     *
     * @param mixed $instance
     */
    public static function swap($instance)
    {
        static::$resolvedInstance[static::getInstanceIdentifier()] = $instance;
    }

    /**
     * Initiate a mock expectation on the facade.
     *
     * @param  dynamic
     *
     * @return \Mockery\Expectation
     */
    public static function shouldReceive()
    {
        $name = static::getInstanceIdentifier();

        if (static::isMock()) {
            $mock = static::$resolvedInstance[$name];
        } else {
            $mock = static::createFreshMockInstance($name);
        }

        return call_user_func_array([$mock, 'shouldReceive'], func_get_args());
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getStaticalProxyRoot()
    {
        return static::resolveStaticalProxyInstance(static::getInstanceIdentifier());
    }

    /**
     * Clear a resolved facade instance.
     *
     * @param string $name
     */
    public static function clearResolvedInstance($name)
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all of the resolved instances.
     */
    public static function clearResolvedInstances()
    {
        static::$resolvedInstance = [];
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getStaticalProxyRoot();

        if (!$instance) {
            throw new RuntimeException('A statical proxy root has not been set.');
        }

        return $instance->$method(...$args);
    }

    /**
     * Resolve the statical proxy root instance from the app.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected static function resolveStaticalProxyInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$container->get($name);
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     * @param string $name
     *
     * @return \Mockery\MockInterface
     */
    protected static function createFreshMockInstance($name)
    {
        static::$resolvedInstance[$name] = $mock = static::createMock();

        $mock->shouldAllowMockingProtectedMethods();

        return $mock;
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     *
     * @return \Mockery\MockInterface
     */
    protected static function createMock()
    {
        $class = static::getMockableClass();

        return $class ? Mockery::mock($class) : Mockery::mock();
    }

    /**
     * Determines whether a mock is set as the instance of the facade.
     *
     * @return bool
     */
    protected static function isMock()
    {
        $name = static::getInstanceIdentifier();

        return isset(static::$resolvedInstance[$name]) &&
                static::$resolvedInstance[$name] instanceof MockInterface;
    }

    /**
     * Get the mockable class for the bound instance.
     *
     * @return string|null
     */
    protected static function getMockableClass()
    {
        if ($root = static::getStaticalProxyRoot()) {
            return get_class($root);
        }
    }
}
