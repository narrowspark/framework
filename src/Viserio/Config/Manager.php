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
     * @param array $array
     */
    public function setArray(array $array)
    {
        $this->repository->setArray($array);
    }

    /**
     * Get the configuration loader.
     *
     * @param \Viserio\Contracts\Config\Loader $loader
     *
     * @return self
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
     * Import configuation from file.
     * Can be grouped together.
     *
     * @param string      $file
     * @param string|null $group
     *
     * @return self
     */
    public function import($file, $group = null)
    {
        $config = $this->loader->load($file, $group);

        $this->repository->setArray($config);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if (!$this->offsetExists($key)) {
            return $default;
        }

        return is_callable($this->offsetGet($key)) ?
            call_user_func($this->offsetGet($key)) :
            $this->offsetGet($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
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
