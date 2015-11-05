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

/**
 * Helper.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class Helper
{
    /**
     * Escape HTML entities in a string.
     *
     * @param string $value
     *
     * @return string
     */
    public static function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Get the root Facade application instance.
     *
     * @param string|null $make
     *
     * @return mixed
     */
    public static function app($make = null)
    {
        if ($make !== null) {
            return self::app()->make($make);
        }

        return StaticalProxyManager::getFacadeApplication();
    }

    /**
     * Get the path to the application folder.
     *
     * @param string $path
     *
     * @return string
     */
    public static function appPath($path = '')
    {
        return self::app('path').($path ? '/'.$path : $path);
    }

    /**
     * Get the path to the storage folder.
     *
     * @param string $path
     *
     * @return string
     */
    public static function storagePath($path = '')
    {
        return self::app('path.storage').($path ? '/'.$path : $path);
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param string|object $class
     *
     * @return string
     */
    public static function classBasename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param string $search
     * @param array  $replace
     * @param string $subject
     *
     * @return string
     */
    public static function strReplaceArray($search, array $replace, $subject)
    {
        foreach ($replace as $value) {
            $subject = preg_replace('/'.$search.'/', $value, $subject, 1);
        }

        return $subject;
    }

    /**
     * Returns all traits used by a class, it's subclasses and trait of their traits.
     *
     * @param string $class
     *
     * @return array
     */
    public static function classUsesRecursive($class)
    {
        $results = [];

        foreach (array_merge([$class => $class], class_parents($class)) as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }

    /**
     * A timing safe equals comparison.
     *
     * To prevent leaking length information, it is important
     * that user input is always used as the second parameter.
     * Based on code by Anthony Ferrara.
     *
     * @see http://blog.ircmaxell.com/2012/12/seven-ways-to-screw-up-bcrypt.html
     *
     * @param string $safe The internal (safe) value to be checked
     * @param string $user The user submitted (unsafe) value
     *
     * @return bool True if the two strings are identical.
     */
    public static function timingSafe($safe, $user)
    {
        /* Prevent issues if string length is 0. */
        $safe .= chr(0);
        $user .= chr(0);

        $safeLen = strlen($safe);
        $userLen = strlen($user);

        /* Set the result to the difference between the lengths. */
        $result = $safeLen - $userLen;

        for ($i = 0; $i < $userLen; $i++) {
            $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }

    /**
     * You can call private/protected methods with getClosure.
     *
     * @param object $object Class
     * @param string $method private/protected method
     * @param array  $args
     *
     * @return mixed
     */
    public static function callPrivateMethod($object, $method, array $args = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $closure = $reflection->getMethod($method)->getClosure($object);

        return call_user_func_array($closure, $args);
    }

    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     */
    public static function dump()
    {
        array_map(function ($x) {
            $dumper = self::app('dump');
            $dumper->dump($x);
        }, func_get_args());

        die(1);
    }

    /**
     * Return the given object. Useful for chaining.
     *
     * @param  $object
     *
     * @return object
     */
    public static function with($object)
    {
        return $object;
    }

    /**
     * Get an item from an object using "dot" notation.
     *
     * @param object $object
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function objectGet($object, $key, $default = null)
    {
        if ($key === null || trim($key) === '') {
            return $object;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return self::value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }

    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function env($key, $default = null)
    {
        $value = \Dotenv::findEnvironmentVariable($key);

        if ($value === false) {
            return self::value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'null':
            case '(null)':
                return;

            case 'empty':
            case '(empty)':
                return '';
        }

        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Check value to find if it was serialized.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isSerialized($value)
    {
        if (!is_string($value)) {
            return false;
        }

        if ($value === 'N;') {
            return true;
        }

        if (strlen($value) < 4) {
            return false;
        }

        if ($value[1] !== ':') {
            return false;
        }

        $lastc = substr($value, -1);

        if ($lastc !== ';' && $lastc !== '}') {
            return false;
        }

        return true;
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}
