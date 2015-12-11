<?php
namespace Viserio\Support;

class Arr
{
    /**
     * Dotted array cache.
     *
     * @var array
     */
    protected static $dotted = [];

    /**
     * Add an element to an array if it doesn't exist.
     *
     * @param array  $array
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    public static function add($array, $key, $value)
    {
        if (!isset($array[$key])) {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Swap two elements between positions.
     *
     * @param array  $array array to swap
     * @param string $swapA
     * @param string $swapB
     *
     * @return array|null
     */
    public static function swap($array, $swapA, $swapB)
    {
        list($array[$swapA], $array[$swapB]) = [$array[$swapB], $array[$swapA]];
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param array $array
     *
     * @return array
     */
    public static function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $cache = serialize(['array' => $array, 'prepend' => $prepend]);

        if (array_key_exists($cache, static::$dotted)) {
            return static::$dotted[$cache];
        }

        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, self::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return static::$dotted[$cache] = $results;
    }

    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param array    $array
     * @param string[] $keys
     *
     * @return array
     */
    public static function except($array, $keys)
    {
        if (self::isColumned($array)) {
            return array_map(function ($array) use ($keys) {
                return self::except($array, $keys);
            }, $array);
        }

        static::forget($array, $keys);

        return $array;
    }

    /**
     * Determines if an array is columned.
     *
     * An array is "columned" if it is index, and it's items are associative arrays using the same keys.
     *
     * @param  array  $array
     * @return bool
     */
    public static function isColumned(array $array)
    {
        if (count($array) && ! self::isAssoc($array) && is_array($array[0]) && self::isAssoc($array[0])) {
            if (count($array) > 1) {
                $item_keys = array_map(function ($item) {
                    $keys = array_keys($item);
                    sort($keys);

                    return implode('|', $keys);
                }, $array);

                return count(array_unique($item_keys)) === 1;
            }

            return true;
        }

        return false;
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array    $array
     * @param callable $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public static function first($array, callable $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return value($default);
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array    $array
     * @param callable $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public static function arrayLast($array, callable $callback, $default = null)
    {
        return self::first(array_reverse($array), $callback, $default);
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array        $array
     * @param array|string $keys
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        $keys     = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            $parts = explode('.', $key);
            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param array|\ArrayAccess $array
     *
     * @return array
     */
    public static function collapse($array)
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            }

            if (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed           $target
     * @param string|callable $key
     * @param mixed           $default
     *
     * @return mixed
     */
    public static function get($target, $key = null, $default = null)
    {
        if ($key === null) {
            return $target;
        }

        if (isset($target[$key])) {
            return $target[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return self::value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return self::value($default);
                }

                $target = $target->{$segment};
            } else {
                return self::value($default);
            }
        }

        return $target;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed  $target
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    public function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if (!is_array($target) && !$target instanceof \ArrayAccess) {
                    return $default;
                }

                $result = self::pluck($target, $key);

                return in_array('*', $key, true) ? self::collapse($result) : $result;
            }

            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif ($target instanceof \ArrayAccess) {
                if (!isset($target[$segment])) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return value($default);
                }

                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param array  $array
     * @param string $key
     *
     * @return bool
     */
    public static function has(array $array, $key)
    {
        if ($array === '' || $key === null) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param string[] $array
     * @param string[] $keys
     *
     * @return string[]
     */
    public static function only($array, $keys)
    {
        if (self::isColumned($array)) {
            return array_map(function ($array) use ($keys) {
                return self::only($array, $keys);
            }, $array);
        }

        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param array|\ArrayAccess $array
     * @param string             $value
     * @param string|null        $key
     *
     * @return array
     */
    public static function pluck(array $array, $value, $key = null)
    {
        $results = [];

        list($value, $key) = static::explodePluckParameters($value, $key);

        // If the key is "null", we will just append the value to the array and keep
        // looping. Otherwise we will key the array using the value of the key we
        // received from the developer. Then we'll return the final array form.
        if (is_null($key)) {
            foreach ($array as $item) {
                $results[] = self::dataGet($item, $value);
            }
        } else {
            foreach ($array as $item) {
                $results[self::dataGet($item, $key)] = self::dataGet($item, $value);
            }
        }

        return $results;
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param string      $value
     * @param string|null $key
     *
     * @return array
     */
    protected static function explodePluckParameters($value, $key)
    {
        $value = is_array($value) ? $value : explode('.', $value);

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array       $array
     * @param string      $key
     * @param string|null $default
     *
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = self::get($array, $key, $default);

        self::forget($array, $key);

        return $value;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if ($key === null) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Filter the array using the given Closure.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public static function where(array $array, callable $callback)
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Check structure of an array.
     * This method checks the structure of an array (only the first layer of it) against
     * a defined set of rules.
     *
     * @param array $array     Array to check.
     * @param array $structure
     *                         Expected array structure. Defined for example like this:
     *                         array(
     *                         'string' => array(
     *                         'callback' => 'strlen',
     *                         'params'   => array('%val'),
     *                         'match'    => 3,
     *                         ),
     *                         'not allowed' = false, // Only makes sense with $strict = false
     *                         'needed'      = true,
     *                         ),
     * @param bool  $strict    If strict is set to false we will allow keys that's not defined in the structure.
     *
     * @return bool Returns true on match, and false on mismatch.
     */
    public static function check(array $array, $structure, $strict = true)
    {
        $success = true;
        /* First compare the size of the two arrays. Return error if strict is enabled. */
        if (count($array) !== count($structure) && $strict === true) {
            //Array does not match defined structure
            return false;
        }

        /* Loop trough all the defined keys defined in the structure. */
        foreach ($structure as $key => $callbackArray) {
            if (isset($array[$key])) {
                /* The key exists in the array we are checking. */

                if (is_array($callbackArray) && isset($callbackArray['callback'])) {
                    /* We have a callback. */

                    /* Replace %val with the acutal value of the key. */
                    $callbackArray['params'] = str_replace('%val', $array[$key], $callbackArray['params']);

                    if (
                        call_user_func_array(
                            $callbackArray['callback'],
                            $callbackArray['params']
                        ) !== $callbackArray['match']) {
                        // Call the *duh* callback. If this returns false throw error,
                        // or an axe.
                        //
                        // Array does not match defined structure
                        // The '.$key.' key did not pass the '.$callbackArray['callback'].' callback');
                        $success = false;
                    }
                } elseif ($callbackArray === false) {
                    // We don't have a callback, but we have found a disallowed key.
                    // Array does not match defined structure. '.$key.' is not allowed
                    $success = false;
                }
            } else {
                // The key don't exist in the array we are checking.
                if ($callbackArray !== false) {
                    // As long as this is not a disallowed key, sound the general alarm.
                    // Array does not match defined structure. '.$key.' not defined
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Flatten a nested array to a separated key.
     *
     * @param array       $array
     * @param string|null $separator
     * @param string      $prepend
     *
     * @return array
     */
    public static function flatten(array $array, $separator = null, $prepend = '')
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = array_merge($flattened, static::flatten($value, $separator, $prepend.$key.$separator));
            } else {
                $flattened[$prepend.$key] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param string $pattern
     * @param array  $replacements
     * @param string $subject
     *
     * @return string
     */
    public static function pregReplaceSub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, function ($match) use (&$replacements) {
            return array_shift($replacements);

        }, $subject);
    }

    /**
     * A shorter way to run a match on the array's keys rather than the values.
     *
     * @param string $pattern
     * @param array  $input
     * @param int    $flags
     *
     * @return array
     */
    public static function pregGrepKeys($pattern, array $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * Index the array by array of keys.
     *
     * @param array     $data
     * @param array     $keys
     * @param bool|true $unique
     *
     * @return array
     */
    public static function getIndexedByKeys(array $data, array $keys, $unique = true)
    {
        $result = [];

        foreach ($data as $value) {
            static::indexByKeys($result, $value, $keys, $unique);
        }

        return $result;
    }

    /**
     * Converts array of arrays to one-dimensional array, where key is $keyName and value is $valueName.
     *
     * @param  array  $array
     * @param  string  $keyName
     * @param  string|array  $valueName
     * @return array
     */
    public static function getIndexedValues(array $array, $keyName, $valueName)
    {
        array_flip(self::pluck($array, $keyName, $valueName));
    }

    /**
     * @param array $result
     * @param array $toSave
     * @param array $keys
     *
     * @param bool|true $unique
     */
    protected static function indexByKeys(array &$result, array $toSave, array $keys, $unique = true)
    {
        foreach ($keys as $key) {
            if (! isset($result[$toSave[$key]])) {
                $result[$toSave[$key]] = [];
            }

            $result = &$result[$toSave[$key]];
        }

        if ($unique) {
            $result = $toSave;
        } else {
            $result[] = $toSave;
        }
    }

    /**
     * Return the given object. Useful for chaining.
     *
     * @param  $object
     *
     * @return mixed
     */
    protected static function with($object)
    {
        return $object;
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
