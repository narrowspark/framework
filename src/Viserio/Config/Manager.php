<?php
namespace Viserio\Config;

use IteratorAggregate;
use Viserio\Contracts\Config\Loader as LoaderContract;
use Viserio\Contracts\Config\Manager as ManagerContract;
use Viserio\Contracts\Config\Repository as RepositoryContract;

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
     * @var \Viserio\Contracts\Config\Loader
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
     * @param RepositoryContract $repository
     */
    public function __construct(RepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Set Viserio's defaults using the repository.
     *
     * @param array $values
     */
    public function setArray(array $values)
    {
        $this->repository->setArray($values);
    }

    /**
     * Get the configuration loader.
     *
     * @param \Viserio\Contracts\Config\Loader $loader
     *
     * @return \Viserio\Contracts\Config\Loader
     */
    public function setLoader(LoaderContract $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Get the configuration loader.
     *
     * @return \Viserio\Contracts\Config\Loader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Load the given configuration group.
     *
     * @param string      $file
     * @param string|null $group
     *
     * @return self
     */
    public function bind($file, $group = null)
    {
        $config = $this->loader->load($file, $group);

        $this->setArray($config);

        return $this;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Get a value.
     *
     * @param string            $key
     * @param string|array|null $default
     *
     * @return mixed The value of a setting
     */
    public function get($key, $default = null)
    {
        if (!$this->offsetExists($key)) {
            return $default;
        }

        return is_callable($this->repository[$key]) ?
            call_user_func($this->repository[$key]) :
            $this->repository[$key];
    }

    /**
     * Set a value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Push a value / array in a multidimensional array.
     *
     * @param string       $key
     * @param string|array $items
     *
     * @return self
     */
    public function push($key, $items)
    {
        array_push($this->repository[$key], $items);

        return $this;
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
     * @return self
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
    public function getIterator()
    {
        return $this->repository->getIterator();
    }

    /**
     * Call a method from repository.
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public function __call($method, array $params = [])
    {
        if ($this->loader && method_exists($this->loader, $method)) {
            return call_user_func_array([$this->loader, $method], $params);
        }

        return call_user_func_array([$this->repository, $method], $params);
    }
}
