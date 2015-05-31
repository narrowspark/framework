<?php

namespace Brainwave\Support;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Application\Application;
use Mockery\MockInterface;

/**
 * StaticalProxyManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
abstract class StaticalProxyManager
{
    /**
     * The application instance being facaded.
     *
     * @var \Brainwave\Application\Application
     */
    protected static $app;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * Hotswap the underlying instance behind the facade.
     *
     * @param mixed $instance
     */
    public static function swap($instance)
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = $instance;

        static::$app[static::getFacadeAccessor()] = $instance;
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
        $name = static::getFacadeAccessor();

        if (static::isMock()) {
            $mock = static::$resolvedInstance[$name];
        } else {
            $mock = static::createFreshMockInstance($name);
        }

        return call_user_func_array([$mock, 'shouldReceive'], func_get_args());
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
        static::$resolvedInstance[$name] = $mock = static::createMockByName($name);

        if (isset(static::$app)) {
            static::$app[$name] = $mock;
        }

        return $mock;
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     * @param string $name
     *
     * @return \Mockery\MockInterface
     */
    protected static function createMockByName($name)
    {
        $staticClass = static::getMockableClass();
        $class = $staticClass($name);

        return $class ? \Mockery::mock($class) : \Mockery::mock();
    }

    /**
     * Determines whether a mock is set as the instance of the facade.
     *
     * @return bool
     */
    protected static function isMock()
    {
        $name = static::getFacadeAccessor();

        return isset(static::$resolvedInstance[$name]) &&
                static::$resolvedInstance[$name] instanceof MockInterface;
    }

    /**
     * Get the mockable class for the bound instance.
     *
     * @return string
     */
    protected static function getMockableClass()
    {
        if ($root = static::getFacadeRoot()) {
            return get_class($root);
        }

        return '';
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Get the registered name of the component.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Resolve the facade root instance from the app.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$app[$name];
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
     * Get the application instance behind the facade.
     *
     * @return \Brainwave\Application\Application
     */
    public static function getFacadeApplication()
    {
        return static::$app;
    }

    /**
     * Set the application instance.
     *
     * @param \Brainwave\Application\Application $app
     */
    public static function setFacadeApplication(Application $app)
    {
        self::$app = $app;
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
        $instance = static::resolveFacadeInstance(static::getFacadeAccessor());

        switch (count($args)) {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
}
