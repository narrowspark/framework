<?php

namespace Brainwave\View;

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

use Brainwave\Contracts\Support\Arrayable;
use Brainwave\Contracts\Support\Renderable;
use Brainwave\Contracts\View\Engine as EngineContract;
use Brainwave\Contracts\View\View as ViewContract;
use Brainwave\Support\Str;

/**
 * View.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class View implements \ArrayAccess, ViewContract
{
    /**
     * The view factory instance.
     *
     * @var \Brainwave\View\Factory
     */
    protected $factory;

    /**
     * The engine implementation.
     *
     * @var \Brainwave\Contracts\View\Engine
     */
    protected $engine;

    /**
     * The name of the view.
     *
     * @var string
     */
    protected $view;

    /**
     * The array of view data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The path to the view file.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new view instance.
     *
     * @param \Brainwave\View\Factory          $factory
     * @param \Brainwave\Contracts\View\Engine $engine
     * @param string                           $view
     * @param string                           $path
     * @param array                            $data
     */
    public function __construct(Factory $factory, EngineContract $engine, $view, $path, $data = [])
    {
        $this->view = $view;
        $this->path = $path;
        $this->engine = $engine;
        $this->factory = $factory;

        $this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;
    }

    /**
     * Get the string contents of the view.
     *
     * @param null|\Closure $callback
     *
     * @return string
     */
    public function render(\Closure $callback = null)
    {
        $contents = $this->getContents();
        $response = isset($callback) ? $callback($this, $contents) : null;

        return $response ?: $contents;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @return string
     */
    protected function getContents()
    {
        return $this->engine->get($this->path, $this->gatherData());
    }

    /**
     * Get the data bound to the view instance.
     *
     * @return array
     */
    protected function gatherData()
    {
        $data = array_merge($this->factory->getShared(), $this->data);

        foreach ($data as $key => $value) {
            if ($value instanceof Renderable) {
                $data[$key] = $value->render();
            }
        }

        return $data;
    }

    /**
     * Add a piece of data to the view.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Add a view instance to the view data.
     *
     * @param string $key
     * @param string $view
     * @param array  $data
     *
     * @return $this
     */
    public function nest($key, $view, array $data = [])
    {
        return $this->with($key, $this->factory->make($view, $data));
    }

    /**
     * Get the view factory instance.
     *
     * @return \Brainwave\View\Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Get the view's rendering engine.
     *
     * @return EngineContract
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name()
    {
        return $this->getName();
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function getName()
    {
        return $this->view;
    }

    /**
     * Get the array of view data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the path to the view file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path to the view.
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Determine if a piece of data is bound.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of bound data to the view.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Unset a piece of data from the view.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get a piece of data from the view.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
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
     * Remove a piece of bound data from the view.
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
     * Dynamically bind parameters to the view.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return \Brainwave\View\View
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }

        throw new \BadMethodCallException(sprintf('Method [%s] does not exist on view.', $method));
    }

    /**
     * Get the string contents of the view.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
