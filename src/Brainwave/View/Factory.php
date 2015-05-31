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

use Brainwave\Contracts\Cache\Factory as CacheContract;
use Brainwave\Contracts\Config\Manager as ConfigContract;
use Brainwave\Contracts\Support\Arrayable;
use Brainwave\Contracts\View\Factory as FactoryContract;
use Brainwave\Contracts\View\Finder as FinderContract;
use Brainwave\Support\Arr;
use Brainwave\Support\Str;
use Brainwave\View\Engines\EngineResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Factory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class Factory implements FactoryContract
{
    /**
     * Config instance.
     *
     * @var \Brainwave\Config\Manager
     */
    protected $config;

    /**
     * Cache instance.
     *
     * @var \Brainwave\Contracts\Cache\Factory
     */
    protected $cache;

    /**
     * The engines instance.
     *
     * @var \Brainwave\View\Engines\EngineResolver
     */
    protected $engines;

    /**
     * The view finder implementation.
     *
     * @var \Brainwave\Contracts\View\Finder
     */
    protected $finder;

    /**
     * The event dispatcher instance.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $events;

    /**
     * Array of registered view name aliases.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * All of the registered view names.
     *
     * @var array
     */
    protected $names = [];

    /**
     * Debug.
     *
     * @var string
     */
    protected $debug;

    /**
     * Register a view extension.
     *
     * @var array
     */
    protected $extensions = [
        'php' => 'php',
        'phtml' => 'php',
        'html' => 'html',
    ];

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Constructor.
     *
     * @param \Brainwave\View\Engines\EngineResolver                      $engines
     * @param \Brainwave\Contracts\View\Finder                            $finder
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $events
     */
    public function __construct(
        EngineResolver $engines,
        FinderContract $finder,
        EventDispatcherInterface $events
    ) {
        $this->engines = $engines;
        $this->finder = $finder;
        $this->events = $events;

        $this->share('__env', $this);

        if ($this->config && ($items = $this->getConfig()->get('view::items')) !== null) {
            $this->shared = array_merge($items, $this->shared);
        }
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Brainwave\View\View
     */
    public function file($path, $data = [], $mergeData = [])
    {
        $data = array_merge($mergeData, $this->parseData($data));

        $engine = explode('|', $path);
        $viewEngine = isset($engine[1]) ? $this->getEngineFromPath($engine[1]) : $this->getEngineFromPath($path);

        $this->callCreator($view = new View($this, $viewEngine, $path, $path, $data));

        return $view;
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Brainwave\View\View
     */
    public function make($view, $data = [], $mergeData = [])
    {
        if (isset($this->aliases[$view])) {
            $view = $this->aliases[$view];
        }

        $view = $this->normalizeName($view);
        $path = $this->finder->find($view);

        return $this->file($path, $data, $mergeData);
    }

    /**
     * Get the evaluated view contents for a named view.
     *
     * @param string   $view
     * @param string[] $data
     *
     * @return \Brainwave\View\View
     */
    public function of($view, $data = [])
    {
        return $this->make($this->names[$view], $data);
    }

    /**
     * Register a named view.
     *
     * @param string $view
     * @param string $name
     */
    public function name($view, $name)
    {
        $this->names[$name] = $view;
    }

    /**
     * Add an alias for a view.
     *
     * @param string $view
     * @param string $alias
     */
    public function alias($view, $alias)
    {
        $this->aliases[$alias] = $view;
    }

    /**
     * Determine if a given view exists.
     *
     * @param string $view
     *
     * @return bool
     */
    public function exists($view)
    {
        try {
            $this->finder->find($view);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Cache or return content from a content section.
     *
     * @param string $key
     * @param bool   $condition
     */
    public function cache($key, $condition = true, callable $callable)
    {
        if (!$condition) {
            return $callable();
        }

        if (!$content = $this->getCache()->get($key)) {
            ob_start();

            $callable();

            $content = ob_get_contents();

            ob_end_clean();

            $this->getCache()->forever($key, $content);
        }

        return $content;
    }

    /**
     * Get the rendered contents of a partial from a loop.
     *
     * @param string $view
     * @param array  $data
     * @param string $iterator
     * @param string $empty
     *
     * @return string
     */
    public function renderEach($view, array $data, $iterator, $empty = 'raw|')
    {
        $result = '';
        // If is actually data in the array, we will loop through the data and append
        // an instance of the partial view to the final result HTML passing in the
        // iterated value of this data array, allowing the views to access them.
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $data = ['key' => $key, $iterator => $value];
                $result .= $this->make($view, $data)->render();
            }

        // If there is no data in the array, we will render the contents of the empty
        // view. Alternatively, the "empty view" could be a raw string that begins
        // with "raw|" for convenience and to let this know that it is a string.
        } else {
            if (Str::startsWith($empty, 'raw|')) {
                $result = substr($empty, 4);
            } else {
                $result = $this->make($empty)->render();
            }
        }

        return $result;
    }

    /**
     * Get the appropriate view engine for the given path.
     *
     * @param string $path
     *
     * @throws \InvalidArgumentException
     *
     * @return \Brainwave\Contracts\View\Engine
     */
    public function getEngineFromPath($path)
    {
        if (!$extension = $this->getExtension($path)) {
            throw new \InvalidArgumentException(sprintf('Unrecognized extension in file: [%s]', $path));
        }

        $engine = $this->extensions[$extension];

        return $this->engines->resolve($engine);
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function share($key, $value = null)
    {
        if (!is_array($key)) {
            return $this->shared[$key] = $value;
        }

        foreach ($key as $innerKey => $innerValue) {
            $this->share($innerKey, $innerValue);
        }
    }

    /**
     * Call the creator for a given view.
     *
     * @param \Brainwave\View\View $view
     */
    public function callCreator(View $view)
    {
        $this->events->addListener('creating: '.$view->getName(), [$view]);
    }

    /**
     * Add a location to the array of view locations.
     *
     * @param string $location
     */
    public function addLocation($location)
    {
        $this->finder->addLocation($location);
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function addNamespace($namespace, $hints)
    {
        $this->finder->addNamespace($namespace, $hints);
    }

    /**
     * Prepend a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function prependNamespace($namespace, $hints)
    {
        $this->finder->prependNamespace($namespace, $hints);
    }

    /**
     * Register a valid view extension and its engine.
     *
     * @param string        $extension
     * @param string        $engine
     * @param \Closure|null $resolver
     */
    public function addExtension($extension, $engine, $resolver = null)
    {
        $this->finder->addExtension($extension);

        if (isset($resolver)) {
            $this->engines->register($engine, $resolver);
        }

        unset($this->extensions[$extension]);

        $this->extensions = array_merge([$extension => $engine], $this->extensions);
    }

    /**
     * Get the extension to engine bindings.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get the engine resolver instance.
     *
     * @return \Brainwave\View\Engines\EngineResolver
     */
    public function getEngineResolver()
    {
        return $this->engines;
    }

    /**
     * Get the view finder instance.
     *
     * @return \Brainwave\Contracts\View\Finder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->events;
    }

    /**
     * Get the config manager instance.
     *
     * @return \Brainwave\Contracts\Config\Manager
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the config manager instance.
     *
     * @param \Brainwave\Contracts\Config\Manager $config
     */
    public function setConfig(ConfigContract $config)
    {
        $this->config = $config;
    }

    /**
     * Get the cache manager instance.
     *
     * @return \Brainwave\Contracts\Cache\Factory
     */
    public function getCache()
    {
        return $this->cahce;
    }

    /**
     * Set the cache manager instance.
     *
     * @param \Brainwave\Contracts\Cache\Factory $cache
     */
    public function setCache(CacheContract $cache)
    {
        $this->cahce = $cahce;
    }

    /**
     * Get an item from the shared data.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function shared($key, $default = null)
    {
        return Arr::get($this->shared, $key, $default);
    }

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Get all of the registered named views in environment.
     *
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Normalize a view name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName($name)
    {
        $delimiter = FinderContract::HINT_PATH_DELIMITER;

        if (strpos($name, $delimiter) === false) {
            return str_replace('/', '.', $name);
        }

        list($namespace, $name) = explode($delimiter, $name);

        return $namespace.$delimiter.str_replace('/', '.', $name);
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

    /**
     * Get the extension used by the view file.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getExtension($path)
    {
        $extensions = array_keys($this->extensions);

        return Arr::first($extensions, function ($key, $value) use ($path) {
            return Str::endsWith($path, $value);
        });
    }
}
