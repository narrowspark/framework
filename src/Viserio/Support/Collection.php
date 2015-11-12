<?php
namespace Viserio\Support;

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

use Viserio\Contracts\Encrypter\Encrypter as EncrypterContract;
use Viserio\Contracts\Support\Arrayable;
use Viserio\Contracts\Support\Jsonable;

/**
 * Collection.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class Collection implements
    \ArrayAccess,
    Arrayable,
    \Countable,
    \IteratorAggregate,
    Jsonable,
    \JsonSerializable
{
    /**
     * Key-value array of data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param mixed $data Pre-populate collection with this key-value array
     */
    public function __construct($data = [])
    {
        $this->data =  $this->getArrayableItems($data);
    }

    /**
     * Set data key to value.
     *
     * @param string $key   The data key
     * @param string $value The data value
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param mixed $items
     *
     * @return static
     */
    public static function make($items = null)
    {
        return new static ($items);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Get data value with key.
     *
     * @param string $key     The data key
     * @param mixed  $default The value to return if data key does not exist
     *
     * @return mixed The data value, or the default value
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return $default;
    }

    /**
     * Get the max value of a given key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function max($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = Arr::dataGet($item, $key);

            return is_null($result) || $item->{$key} > $result ? $item->{$key} : $result;
        });
    }

    /**
     * Get the min value of a given key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function min($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = Arr::dataGet($item, $key);

            return is_null($result) || $value < $result ? $value : $result;
        });
    }

    /**
     * Collapse the collection items into a single array.
     *
     * @return static
     */
    public function collapse()
    {
        return new static (Arr::collapse($this->data));
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param mixed $key
     * @param int   $value
     *
     * @return bool
     */
    public function contains($key, $value = null)
    {
        if (func_num_args() === 2) {
            return $this->contains(function ($k, $item) use ($key, $value) {
                return Arr::get($item, $key) === $value;
            });
        }

        if ($this->useAsCallable($key)) {
            return null !== $this->first($key);
        }

        return in_array($key, $this->data, true);
    }

    /**
     * Diff the collection with the given items.
     *
     * @param mixed $items
     *
     * @return static
     */
    public function diff($items)
    {
        return new static (array_diff($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Execute a callback over each item.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->data as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback === null) {
            return new static(array_filter($this->items));
        }

        return new static(array_filter($this->items, $callback));
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param string $key
     * @param int    $value
     * @param bool   $strict
     *
     * @return static
     */
    public function where($key, $value, $strict = true)
    {
        return $this->filter(function ($item) use ($key, $value, $strict) {
            return $strict ? Arr::get($item, $key) === $value : Arr::get($item, $key) === $value;
        });
    }

    /**
     * Filter items by the given key value pair using loose comparison.
     *
     * @param string $key
     * @param int    $value
     *
     * @return static
     */
    public function whereLoose($key, $value)
    {
        return $this->where($key, $value, false);
    }

    /**
     * Get the first item from the collection.
     *
     * @param null|callable $callback
     * @param string        $default
     *
     * @return mixed|null
     */
    public function first(callable $callback = null, $default = null)
    {
        if ($callback === null) {
            return count($this->data) > 0 ? end($this->data) : null;
        }

        return Arr::first($this->data, $callback, $default);
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @return static
     */
    public function flatten()
    {
        return new static (Arr::flatten($this->data));
    }

    /**
     * Flip the items in the collection.
     *
     * @return static
     */
    public function flip()
    {
        return new static (array_flip($this->data));
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param mixed $key
     *
     * @return $this
     */
    public function forget($key)
    {
        $this->offsetUnset($key);

        return $this;
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->data));
    }

    /* Get the last item from the collection.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function last(callable $callback = null, $default = null)
    {
        return count($this->data) > 0 ? end($this->data) : null;

        if (is_null($callback)) {
            return count($this->data) > 0 ? end($this->data) : Arr::value($default);
        }

        return Arr::last($this->data, $callback, $default);
    }

    /**
     * Get the values of a given key.
     *
     * @param string      $value
     * @param string|null $key
     *
     * @return static
     */
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->data, $value, $key));
    }

    /**
     * Does this set contain a key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove value with key from this set.
     *
     * @param string $key The data key
     */
    public function remove($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Clear all values.
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param callable|string $groupBy
     * @param bool            $preserveKeys
     *
     * @return static
     */
    public function groupBy($groupBy, $preserveKeys = false)
    {
        $groupBy = $this->valueRetriever($groupBy);

        $results = [];

        foreach ($this->data as $key => $value) {
            $groupKey = $groupBy($value, $key);

            if (!array_key_exists($groupKey, $results)) {
                $results[$groupKey] = new static();
            }

            $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
        }

        return new static($results);
    }

    /**
     * Get the "group by" key value.
     *
     * @param callable|string $groupBy
     * @param string          $key
     * @param mixed           $value
     *
     * @return string
     */
    protected function getGroupbyKey($groupBy, $key, $value)
    {
        if (!is_string($groupBy) && is_callable($groupBy)) {
            return $groupBy($value, $key);
        }

        return Arr::get($value, $groupBy);
    }

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param callable|string $keyBy
     *
     * @return static
     */
    public function keyBy($keyBy)
    {
        $keyBy = $this->valueRetriever($keyBy);

        $results = [];

        foreach ($this->data as $item) {
            $results[$keyBy($item)] = $item;
        }

        return new static($results);
    }

    /**
     * Run a map over each of the items.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->data);
        $data = array_map($callback, $this->data, $keys);

        return new static(array_combine($keys, $data));
    }

    /**
     * Push an item onto the beginning of the collection.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($this->items, $value);
        } else {
            $this->items = [$key => $value] + $this->items;
        }

        return $this;
    }

    /**
     * Get all items except for those with the specified keys.
     *
     * @param  mixed  $keys
     * @return static
     */
    public function except($keys)
    {
        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Get the items with the specified keys.
     *
     * @param  mixed  $keys
     * @return static
     */
    public function only($keys)
    {
        return new static(Arr::only($this->items, $keys));
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param \Viserio\Support\Collection $value
     *
     * @return $this
     */
    public function push($value)
    {
        $this->data[] = $value;

        return $this;
    }

    /**
     * Merge the collection with the given items.
     *
     * @param mixed $items
     *
     * @return static
     */
    public function merge($items)
    {
        return new static (array_merge($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed|null
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * Pulls an item from the collection.
     *
     * @param int    $key
     * @param string $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->data, $key, $default);
    }

    /**
     * Put an item in the collection by key.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return $this
     */
    public function put($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param \Closure $callback
     *
     * @return static
     */
    public function reject($callback)
    {
        if ($this->useAsCallable($callback)) {
            return $this->filter(function ($item) use ($callback) {
                return !$callback($item);
            });
        }

        return $this->filter(function ($item) use ($callback) {
            return $item !== $callback;
        });
    }

    /**
     * Reverse items order.
     *
     * @return static
     */
    public function reverse()
    {
        return new static (array_reverse($this->data));
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     *
     * @param mixed $value
     * @param bool  $strict
     *
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        if (!$this->useAsCallable($value)) {
            return array_search($value, $this->data, $strict);
        }

        foreach ($this->data as $key => $item) {
            if (call_user_func($value, $item, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get and remove the first item from the collection.
     *
     * @return mixed|null
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * Shuffle the items in the collection.
     *
     * @return $this
     */
    public function shuffle()
    {
        $items = $this->data;

        shuffle($items);

        return new static($items);
    }

    /**
     * Slice the underlying collection array.
     *
     * @param int      $offset
     * @param int|null $length
     * @param bool     $preserveKeys
     *
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static (array_slice($this->data, $offset, $length, $preserveKeys));
    }

    /**
     * Chunk the underlying collection array.
     *
     * @param int  $size
     * @param bool $preserveKeys
     *
     * @return static
     */
    public function chunk($size, $preserveKeys = false)
    {
        $chunks = new static();

        foreach (array_chunk($this->data, $size, $preserveKeys) as $chunk) {
            $chunks->push(new static ($chunk));
        }

        return $chunks;
    }

    /**
     * Sort through each item with a callback.
     *
     * @param callable|null $callback
     *
     * @return self
     */
    public function sort(callable $callback = null)
    {
        $data = $this->data;

        $callback ? uasort($data, $callback) : natcasesort($data);

        return new static($data);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param callback|string $callback
     * @param int             $options
     * @param bool            $descending
     *
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];

        $callback = $this->valueRetriever($callback);

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($this->data as $key => $value) {
            $results[$key] = $callback($value);
        }

        $descending ? arsort($results, $options)
        : asort($results, $options);

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->data[$key];
        }

        return new static($results);
    }

    /**
     * Sort the collection in descending order using the given Closure.
     *
     * @param \Closure $callback
     * @param int      $options
     *
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param int      $offset
     * @param int|null $length
     * @param string   $replacement
     *
     * @return static
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() === 1) {
            return new static(array_splice($this->data, $offset));
        }

        return new static (array_splice($this->data, $offset, $length, $replacement));
    }

    /**
     * Get the sum of the given values.
     *
     * @param callable|string|null $callback
     *
     * @return mixed
     */
    public function sum($callback = null)
    {
        if ($callback === null) {
            $callback = function ($item) {
                return $item;
            };
        }

        $callback = $this->valueRetriever($callback);

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result += $callback($item);
        }, 0);
    }

    /**
     * Take the first or last {$limit} items.
     *
     * @param int $limit
     *
     * @return static
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Transform each item in the collection using a callback.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->data = $this->map($callback)->all();

        return $this;
    }

    /**
     * Return only unique items from the collection array.
     *
     * @param string|callable|null $key
     *
     * @return static
     */
    public function unique($key = null)
    {
        if (is_null($key)) {
            return new static(array_unique($this->data, SORT_REGULAR));
        }

        $key = $this->valueRetriever($key);

        $exists = [];

        return $this->reject(function ($item) use ($key, &$exists) {
            if (in_array($id = $key($item), $exists, true)) {
                return true;
            }

            $exists[] = $id;
        });
    }

    /**
     * Head the keys on the underlying array.
     *
     * @return static
     */
    public function values()
    {
        $this->data = array_values($this->data);

        return $this;
    }

    /**
     * Get one or more items randomly from the collection.
     *
     * @param int $amount
     *
     * @return mixed
     */
    public function random($amount = 1)
    {
        if ($amount > ($count = $this->count())) {
            throw new \InvalidArgumentException(
                sprintf('You requested [%s] items, but there are only [%s] items in the collection', $amount, $count)
            );
        }

        $keys = array_rand($this->data, $amount);

        if ($amount === 1) {
            return $this->data[$keys];
        }

        return new static(array_intersect_key($this->data, array_flip($keys)));
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;

        }, $this->data);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Encrypt data.
     *
     * @param EncrypterContract $crypt
     */
    public function encrypt(EncrypterContract $crypt)
    {
        foreach ($this->data as $key => $value) {
            $this->set($key, $crypt->encrypt($value));
        }
    }

    /**
     * Decrypt data.
     *
     * @param EncrypterContract $crypt
     */
    public function decrypt(EncrypterContract $crypt)
    {
        foreach ($this->data as $key => $value) {
            $this->set($key, $crypt->decrypt($value));
        }
    }

    /**
     * Ensure two collections are the same.
     *
     * @param \Viserio\Support\Collection $collection
     *
     * @return bool
     */
    public function same(Collection $collection)
    {
        return sha1($this) === sha1($collection);
    }

    /**
     * Does this set contain a key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get data value with key.
     *
     * @param string $key The data key
     *
     * @return mixed The data value
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set data key to value.
     *
     * @param string $key   The data key
     * @param string $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Remove value with key from this set.
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get number of items in collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param mixed $items
     *
     * @return static
     */
    public function intersect($items)
    {
        return new static (array_intersect($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Concatenate values of a given key as a string.
     *
     * @param string      $value
     * @param string|null $glue
     *
     * @return string
     */
    public function implode($value, $glue = null)
    {
        $first = $this->first();

        if (is_array($first) || is_object($first)) {
            return implode($glue, $this->pluck($value)->all());
        }

        return implode($value, $this->data);
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * @param \Viserio\Support\Collection|\Viserio\Contracts\Support\Arrayable|array $items
     *
     * @return static
     */
    public function zip($items)
    {
        $arrayableItems = array_map(function ($items) {
            return $this->getArrayableItems($items);
        }, func_get_args());

        $params = array_merge([function () {
            return new static(func_get_args());
        }, $this->data], $arrayableItems);

        return new static(call_user_func_array('array_map', $params));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param callable $callback
     * @param int|null $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->data, $callback, $initial);
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Get a CachingIterator instance.
     *
     * @param int $flags
     *
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = \CachingIterator::CALL_TOSTRING)
    {
        return new \CachingIterator($this->getIterator(), $flags);
    }

    /**
     * Checks if the collection is associative.
     *
     * @return bool
     */
    public function isAssociative()
    {
        return !$this->isSequential();
    }

    /**
     * Checks if the collection is sequential.
     *
     * @return bool
     */
    public function isSequential()
    {
        return $this->keys()->filter('is_string')->isEmpty();
    }

    /**
     * Get collection iterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Dynamically access the collection attributes.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Dynamically set attribute to set.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Dynamically remove attributes from set.
     *
     * @param string $key
     *
     * @return bool|null
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param mixed $items
     *
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
           return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        }

        return (array) $items;
    }

    /**
     * Get a value retrieving callback.
     *
     * @param callable $value
     *
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return Arr::dataGet($item, $value);
        };
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return !is_string($value) && is_callable($value);
    }
}
