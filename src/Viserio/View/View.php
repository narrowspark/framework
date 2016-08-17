<?php
declare(strict_types=1);
namespace Viserio\View;

use ArrayAccess;
use BadMethodCallException;
use Throwable;
use Viserio\Contracts\Support\Arrayable;
use Viserio\Contracts\Support\Renderable;
use Viserio\Contracts\View\Engine as EngineContract;
use Viserio\Contracts\View\Factory as FactoryContract;
use Viserio\Contracts\View\View as ViewContract;
use Viserio\Support\Str;

class View implements ArrayAccess, ViewContract
{
    /**
     * The view factory instance.
     *
     * @var \Viserio\View\Factory
     */
    protected $factory;

    /**
     * The engine implementation.
     *
     * @var \Viserio\Contracts\View\Engine
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
     * @param \Viserio\View\Factory                      $factory
     * @param \Viserio\Contracts\View\Engine             $engine
     * @param string                                     $view
     * @param string                                     $path
     * @param array|\Viserio\Contracts\Support\Arrayable $data
     */
    public function __construct(Factory $factory, EngineContract $engine, string $view, string $path, $data = [])
    {
        $this->view = $view;
        $this->path = $path;
        $this->engine = $engine;
        $this->factory = $factory;

        $this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;
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
    public function __isset($key): bool
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
     * @return \Viserio\View\View
     */
    public function __call(string $method, array $parameters): ViewContract
    {
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }

        throw new BadMethodCallException(sprintf('Method [%s] does not exist on view.', $method));
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

    /**
     * {@inheritdoc}
     */
    public function render(callable $callback = null): string
    {
        try {
            $contents = $this->getContents();

            $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;

            return $response !== null ? $response : $contents;
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function with($key, $value = null): ViewContract
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function nest($key, string $view, array $data = []): ViewContract
    {
        return $this->with($key, $this->factory->create($view, $data));
    }

    /**
     * {@inheritdoc}
     */
    public function getFactory(): FactoryContract
    {
        return $this->factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getEngine(): EngineContract
    {
        return $this->engine;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->view;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path): ViewContract
    {
        $this->path = $path;

        return $this;
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
     * Get the evaluated contents of the view.
     *
     * @return string
     */
    protected function getContents(): string
    {
        return $this->engine->get($this->path, $this->gatherData());
    }

    /**
     * Get the data bound to the view instance.
     *
     * @return array
     */
    protected function gatherData(): array
    {
        $data = array_merge($this->factory->getShared(), $this->data);

        foreach ($data as $key => $value) {
            if ($value instanceof Renderable) {
                $data[$key] = $value->render();
            }
        }

        return $data;
    }
}
