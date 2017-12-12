<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy;

use Mockery;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\StaticalProxy\Exception\BadMethodCallException;
use Viserio\Component\Contract\StaticalProxy\Exception\RuntimeException;

class StaticalProxy
{
    /**
     * @var \Psr\Container\ContainerInterface the Container that provides the Proxy Subjects
     */
    protected static $container;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance = [];

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \Viserio\Component\Contract\StaticalProxy\Exception\RuntimeException
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::getStaticalProxyRoot();

        if (! $instance) {
            throw new RuntimeException('A statical proxy root has not been set.');
        }

        return $instance->$method(...$args);
    }

    /**
     * Sets the Container that will be used to retrieve the Proxy Subject.
     *
     * @param \Psr\Container\ContainerInterface $container The Container that provides the real Proxy Subject
     */
    public static function setContainer(ContainerInterface $container): void
    {
        static::$container = $container;
    }

    /**
     * Retrieves the instance of the Proxy Subject from the Container that the Static Proxy is associated with.
     *
     * @return mixed
     */
    public static function getInstance()
    {
        return static::$container->get(static::getInstanceIdentifier());
    }

    /**
     * Retrieves the Instance Identifier that is used to retrieve the Proxy Subject from the Container.
     *
     * @throws \Viserio\Component\Contract\StaticalProxy\Exception\BadMethodCallException if the method has not been implemented by a subclass
     *
     * @return object|string
     */
    public static function getInstanceIdentifier()
    {
        throw new BadMethodCallException(\sprintf('The [%s] method must be implemented by a subclass.', __METHOD__));
    }

    /**
     * Hot swap the underlying instance behind the static proxy.
     *
     * @param mixed $instance
     */
    public static function swap($instance): void
    {
        static::$resolvedInstance[static::getInstanceIdentifier()] = $instance;
    }

    /**
     * Initiate a mock expectation on the static proxy.
     *
     * @return \Mockery\CompositeExpectation
     */
    public static function shouldReceive(): CompositeExpectation
    {
        $name = static::getInstanceIdentifier();

        if (static::isMock()) {
            $mock = static::$resolvedInstance[$name];
        } else {
            $mock = static::createFreshMockInstance($name);
        }

        return \call_user_func_array([$mock, 'shouldReceive'], \func_get_args());
    }

    /**
     * Get the root object behind the static proxy.
     *
     * @return null|object
     */
    public static function getStaticalProxyRoot()
    {
        return static::resolveStaticalProxyInstance(static::getInstanceIdentifier());
    }

    /**
     * Clear a resolved static proxy instance.
     *
     * @param string $name
     */
    public static function clearResolvedInstance(string $name): void
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all of the resolved instances.
     */
    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstance = [];
    }

    /**
     * Resolve the statical proxy root instance from the app.
     *
     * @param object|string $name
     *
     * @return object
     */
    protected static function resolveStaticalProxyInstance($name): object
    {
        if (\is_object($name)) {
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
    protected static function createFreshMockInstance(string $name): MockInterface
    {
        static::$resolvedInstance[$name] = $mock = static::createMock();

        $mock->shouldAllowMockingProtectedMethods();

        return $mock;
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     * @return \Mockery\MockInterface
     */
    protected static function createMock(): MockInterface
    {
        if ($root = static::getStaticalProxyRoot()) {
            return Mockery::mock(\get_class($root));
        }
        // @codeCoverageIgnoreStart
        return Mockery::mock();
        // @codeCoverageIgnoreStop
    }

    /**
     * Determines whether a mock is set as the instance of the static proxy.
     *
     * @return bool
     */
    protected static function isMock(): bool
    {
        $name = static::getInstanceIdentifier();

        return isset(static::$resolvedInstance[$name]) &&
                static::$resolvedInstance[$name] instanceof MockInterface;
    }
}
