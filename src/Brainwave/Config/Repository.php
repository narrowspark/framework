<?php

namespace Brainwave\Config;

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

use Brainwave\Contracts\Config\Repository as RepositoryContract;
use Brainwave\Support\Arr;

/**
 * Repository.
 *
 * A default Configuration class which provides app configuration values stored as nested arrays,
 * which can be accessed and stored using dot separated keys.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class Repository implements RepositoryContract
{
    /**
     * Cache of previously parsed keys.
     *
     * @var array
     */
    protected $keys = [];

    /**
     * Storage array of values.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Expected nested key separator.
     *
     * @var string
     */
    protected $separator = '.';

    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set.
     *
     * @param array $values
     * @required
     */
    public function setArray(array $values = [])
    {
        $this->values = $this->mergeArrays($this->values, $values);
    }

    /**
     * Set Separator.
     *
     * @param string $separator
     *
     * @return \Brainwave\Config\Repository
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Get Separator.
     *
     * @return string|null
     */
    public function getSeparator()
    {
        $this->separator;
    }

    /**
     * Get all values as nested array.
     *
     * @return array
     */
    public function getAllNested()
    {
        return $this->values;
    }

    /**
     * Get all values as flattened key array.
     *
     * @return array
     */
    public function getAllFlat()
    {
        return Arr::flatten($this->values, $this->separator);
    }

    /**
     * Get all flattened array keys.
     *
     * @return array
     */
    public function getKeys()
    {
        $flattened = Arr::flatten($this->values, $this->separator);

        return array_keys($flattened);
    }

    /**
     * Get a value from a nested array based on a separated key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->getValue($key, $this->values);
    }

    /**
     * Set nested array values based on a separated key.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return array|null
     */
    public function offsetSet($key, $value)
    {
        $this->setValue($key, $value, $this->values);
    }

    /**
     * Check an array has a value based on a separated key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return (bool) $this->getValue($key, $this->values);
    }

    /**
     * Remove nested array value based on a separated key.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $keys = $this->parseKey($key, $this->separator);
        $array = &$this->values;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                return;
            }

            $array = &$array[$key];
        }

        unset($array[array_shift($keys)]);
    }

    /**
     * Parse a separated key and cache the result.
     *
     * @param string $key
     * @param string $separator
     *
     * @return array
     */
    protected function parseKey($key, $separator)
    {
        if (!isset($this->keys[$key])) {
            $this->keys[$key] = explode($separator, $key);
        }

        return $this->keys[$key];
    }

    /**
     * Get a value from a nested array based on a separated key.
     *
     * @param string $key
     * @param array  $array
     *
     * @return mixed
     */
    protected function getValue($key, array $array = [])
    {
        $keys = $this->parseKey($key, $this->separator);

        while (count($keys) > 0 && $array !== null) {
            $key = array_shift($keys);
            $array = isset($array[$key]) ? $array[$key] : null;
        }

        return $array;
    }

    /**
     * Set nested array values based on a separated key.
     *
     * @param string $key
     * @param mixed  $value
     * @param array  $array
     *
     * @return array
     */
    protected function setValue($key, $value, array &$array = [])
    {
        $keys = $this->parseKey($key, $this->separator);
        $pointer = &$array;

        while (count($keys) > 0) {
            $key = array_shift($keys);
            $pointer[$key] = (isset($pointer[$key]) ? $pointer[$key] : []);
            $pointer = &$pointer[$key];
        }

        $pointer = $value;

        return $array;
    }

    /**
     * Merge arrays with nested keys into the values store
     * Usage: $this->mergeArrays(array $array [, array $...]).
     *
     * @return array
     */
    protected function mergeArrays()
    {
        $args = func_get_args();
        $merged = array_shift($args);

        while (!empty($args)) {
            $next = array_shift($args);

            foreach ($next as $k => $v) {
                if (is_integer($k)) {
                    if (isset($merged[$k])) {
                        $merged[] = $v;
                    } else {
                        $merged[$k] = $v;
                    }
                } elseif (is_array($v) && isset($merged[$k]) && is_array($merged[$k])) {
                    $merged[$k] = $this->mergeArrays($merged[$k], $v);
                } else {
                    $this->setValue($k, $v, $merged);
                }
            }
        }

        return $merged;
    }

    /**
     * Get an ArrayIterator for the stored items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getAllNested());
    }
}
