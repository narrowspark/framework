<?php
declare(strict_types=1);
namespace Viserio\Config;

use ArrayIterator;
use IteratorAggregate;
use Viserio\Contracts\Config\Manager as ManagerContract;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Parsers\Loader as LoaderContract;

class Manager implements ManagerContract, IteratorAggregate
{
    /**
     * Handler for Configuration values.
     *
     * @var mixed
     */
    protected $repository;

    /**
     * Fileloader instance.
     *
     * @var \Viserio\Contracts\Parsers\Loader
     */
    protected $loader;

    /**
     * Config folder path.
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param \Viserio\Contracts\Config\Repository $repository
     */
    public function __construct(RepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Call a method from repository.
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public function __call(string $method, array $params = [])
    {
        if ($this->loader && method_exists($this->loader, $method)) {
            return call_user_func_array([$this->loader, $method], $params);
        }

        return call_user_func_array([$this->repository, $method], $params);
    }

    /**
     * Set Viserio's defaults using the repository.
     *
     * @param array $array
     *
     * @return $this
     */
    public function setArray(array $array): ManagerContract
    {
        $this->repository->setArray($array);

        return $this;
    }

    /**
     * Import configuation from file.
     * Can be grouped together.
     *
     * @param string      $file
     * @param string|null $group
     *
     * @return $this
     */
    public function import(string $file, string $group = null): ManagerContract
    {
        $config = $this->loader->load($file, $group);

        $this->repository->setArray($config);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): ManagerContract
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

        return is_callable($this->offsetGet($key)) ?
            call_user_func($this->offsetGet($key)) :
            $this->offsetGet($key);
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
    public function delete(string $key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Get a value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->repository->offsetGet($key);
    }

    /**
     * Set a value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function offsetSet($key, $value)
    {
        $this->repository->offsetSet($key, $value);

        return $this;
    }

    /**
     * Check a value exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->repository->offsetExists($key);
    }

    /**
     * Remove a value.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $this->repository->offsetUnset($key);
    }

    /**
     * Get an ArrayIterator for the stored items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return $this->repository->getIterator();
    }

    /**
     * Set the file loader.
     *
     * @param \Viserio\Contracts\Parsers\Loader $loader
     *
     * @return $this
     */
    public function setLoader(LoaderContract $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Get the file loader.
     *
     * @return \Viserio\Contracts\Parsers\Loader
     */
    public function getLoader()
    {
        return $this->loader;
    }
}
