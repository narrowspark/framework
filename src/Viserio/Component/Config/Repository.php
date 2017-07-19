<?php
declare(strict_types=1);
namespace Viserio\Component\Config;

use ArrayIterator;
use IteratorAggregate;
use Narrowspark\Arr\Arr;
use RuntimeException;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Parsers\Traits\ParserAwareTrait;

class Repository implements RepositoryContract, IteratorAggregate
{
    use ParserAwareTrait;

    /**
     * Config folder path.
     *
     * @var string
     */
    protected $path;

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
     * {@inheritdoc}
     */
    public function import(string $filepath, array $options = null): RepositoryContract
    {
        if ($this->loader === null && \pathinfo($filepath, PATHINFO_EXTENSION) === 'php') {
            if (! \file_exists($filepath)) {
                throw new RuntimeException(\sprintf('File [%s] not found.', $filepath));
            }

            $config = (array) require $filepath;
        } else {
            $config = $this->getLoader()->load($filepath, $options);
        }

        $this->setArray($config);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): RepositoryContract
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        if (! $this->offsetExists($key)) {
            return $default;
        }

        return $this->offsetGet($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): RepositoryContract
    {
        return $this->offsetUnset($key);
    }

    /**
     * {@inheritdoc}
     */
    public function setArray(array $values = []): RepositoryContract
    {
        $this->data = Arr::merge($this->data, $values);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllFlat(): array
    {
        return Arr::flatten($this->data, '.');
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys(): array
    {
        return \array_keys(Arr::flatten($this->data, '.'));
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
     * @return $this
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
     *
     * @return $this
     */
    public function offsetUnset($key): RepositoryContract
    {
        Arr::forget($this->data, $key);

        return $this;
    }

    /**
     * Get an ArrayIterator for the stored items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getAll());
    }
}
