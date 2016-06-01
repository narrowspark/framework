<?php
namespace Viserio\Config;

use ArrayIterator;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Config\Repository as RepositoryContract;

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
    protected $data = [];

    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set.
     *
     * @param array $values
     *
     * @return self
     */
    public function setArray(array $values = []): RepositoryContract
    {
        $this->data = Arr::merge($this->data, $values);

        return $this;
    }

    /**
     * Get all values as nested array.
     *
     * @return array
     */
    public function getAllNested(): array
    {
        return $this->data;
    }

    /**
     * Get all values as flattened key array.
     *
     * @return array
     */
    public function getAllFlat(): array
    {
        return Arr::flatten($this->data, '.');
    }

    /**
     * Get all as flattened array keys.
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys(Arr::flatten($this->data, '.'));
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
        return Arr::get($this->data, $key);
    }

    /**
     * Set nested array values based on a separated key.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function offsetSet($key, $value): RepositoryContract
    {
        $this->data = Arr::set($this->data, $key, $value);

        return $this;
    }

    /**
     * Check an array has a value based on a separated key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Remove nested array value based on a separated key.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $this->data = Arr::forget($this->data, $key);
    }

    /**
     * Get an ArrayIterator for the stored items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getAllNested());
    }
}
