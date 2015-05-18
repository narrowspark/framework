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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Config\Manager as ManagerContract;
use Brainwave\Contracts\Config\Repository as RepositoryContract;
use Brainwave\Filesystem\FileLoader;

/**
 * Manager.
 *
 * Uses a ConfigurationHandler class to parse configuration data,
 * accessed as an array.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class Manager implements ManagerContract, \IteratorAggregate
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
     * @var mixed
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
     * @param FileLoader         $loader
     */
    public function __construct(RepositoryContract $repository, FileLoader $loader)
    {
        $this->setHandler($repository);
        $this->loader = $loader;
    }

    /**
     * Set Brainwave's defaults using the repository.
     *
     * @param array $values
     */
    public function setArray(array $values)
    {
        $this->repository->setArray($values);
    }

    /**
     * Set a configuration repository and provide it some defaults.
     *
     * @param RepositoryContract $repository
     */
    public function setHandler(RepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the configuration repository for access.
     *
     * @return RepositoryContract
     */
    public function getHandler()
    {
        return $this->repository;
    }

    /**
     * Get the configuration loader.
     *
     * @return \Brainwave\Filesystem\FileLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Load the given configuration group.
     *
     * @param string      $file
     * @param string|null $namespace
     * @param string|null $environment
     * @param string|null $group
     *
     * @return array|null
     */
    public function bind($file, $group = null, $environment = null, $namespace = null)
    {
        $config = $this->loader->load($file, $group, $environment, $namespace);

        $this->setArray($config);
    }

    /**
     * Apply any cascades to an array of package options.
     *
     * @param string      $file
     * @param string|null $package
     * @param string|null $group
     * @param string|null $env
     * @param array|null  $items
     *
     * @return array
     */
    public function cascadePackage($file, $package = null, $group = null, $env = null, $items = null)
    {
        return $this->loader->cascadePackage($file, $package, $group, $env, $items);
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
        return $this->get($key) !== null;
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
        if ($this->repository[$key] === null) {
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
     */
    public function set($key, $value)
    {
        $this->repository[$key] = $value;
    }

    /**
     * Push a value / array in a multidimensional array.
     *
     * @param string       $key
     * @param string|array $items
     */
    public function push($key, $items)
    {
        array_push($this->repository[$key], $items);
    }

    /**
     * Set path to config folder.
     *
     * @param string $path
     *
     * @return self
     */
    public function addPath($path)
    {
        $this->path = $path;
        $this->loader->addDefaultPath($path);

        return $this;
    }

    /**
     * Get config folder path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
        return call_user_func_array([$this->repository, $method], $params);
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
        return $this->repository[$key];
    }

    /**
     * Set a value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->repository[$key] = $value;
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
        return isset($this->repository[$key]);
    }

    /**
     * Remove a value.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->repository[$key]);
    }

    /**
     * Get an ArrayIterator for the stored items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->repository->getAllNested());
    }
}
