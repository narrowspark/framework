<?php
namespace Viserio\View;

use InvalidArgumentException;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\{
    Events\Dispatcher as DispatcherContract,
    Support\Arrayable,
    View\Engine as EngineContract,
    View\Factory as FactoryContract,
    View\Finder as FinderContract,
    View\View as ViewContract
};
use Viserio\Support\Str;
use Viserio\View\{
    Engines\EngineResolver,
    Traits\NormalizeNameTrait
};

class Factory implements FactoryContract
{
    use NormalizeNameTrait;

    /**
     * The engines instance.
     *
     * @var \Viserio\View\Engines\EngineResolver
     */
    protected $engines;

    /**
     * The view finder implementation.
     *
     * @var \Viserio\Contracts\View\Finder
     */
    protected $finder;

    /**
     * The event dispatcher instance.
     *
     * @var \Viserio\Contracts\Events\Dispatcher
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
    ];

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Virtuoso instance.
     *
     * @var Virtuoso
     */
    protected $virtuoso;

    /**
     * Constructor.
     *
     * @param \Viserio\View\Engines\EngineResolver $engines
     * @param \Viserio\Contracts\View\Finder       $finder
     * @param \Viserio\Contracts\Events\Dispatcher $events
     */
    public function __construct(
        EngineResolver $engines,
        FinderContract $finder,
        DispatcherContract $events
    ) {
        $this->engines = $engines;
        $this->finder = $finder;
        $this->events = $events;

        $this->share('__env', $this);
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Viserio\View\View
     */
    public function file(string $path, array $data = [], array $mergeData = []): ViewContract
    {
        $data = array_merge($mergeData, $this->parseData($data));
        $engine = $this->getEngineFromPath($path);

        return $this->getView($this, $engine, $path, $path, $data);
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Viserio\View\View
     */
    public function create(string $view, array $data = [], array $mergeData = []): ViewContract
    {
        if (isset($this->aliases[$view])) {
            $view = $this->aliases[$view];
        }

        $view = $this->normalizeName($view);
        $path = $this->finder->find($view);
        $data = array_merge($mergeData, $this->parseData($data));
        $engine = $this->getEngineFromPath($path);

        return $this->getView($this, $engine, $view, $path, $data);
    }

    /**
     * Get the evaluated view contents for a named view.
     *
     * @param string   $view
     * @param string[] $data
     *
     * @return \Viserio\View\View
     */
    public function of(string $view, array $data = []): ViewContract
    {
        return $this->create($this->names[$view], $data);
    }

    /**
     * Register a named view.
     *
     * @param string $view
     * @param string $name
     */
    public function name(string $view, string $name)
    {
        $this->names[$name] = $view;
    }

    /**
     * Add an alias for a view.
     *
     * @param string $view
     * @param string $alias
     */
    public function alias(string $view, string $alias)
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
    public function exists(string $view): bool
    {
        try {
            $this->finder->find($view);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        return true;
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
    public function renderEach(string $view, array $data, string $iterator, string $empty = 'raw|'): string
    {
        $result = '';

        // If is actually data in the array, we will loop through the data and append
        // an instance of the partial view to the final result HTML passing in the
        // iterated value of this data array, allowing the views to access them.
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $data = ['key' => $key, $iterator => $value];
                $result .= $this->create($view, $data)->render();
            }

        // If there is no data in the array, we will render the contents of the empty
        // view. Alternatively, the "empty view" could be a raw string that begins
        // with "raw|" for convenience and to let this know that it is a string.
        } else {
            if (Str::startsWith($empty, 'raw|')) {
                $result = substr($empty, 4);
            } else {
                $result = $this->create($empty)->render();
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
     * @return \Viserio\Contracts\View\Engine
     */
    public function getEngineFromPath(string $path): \Viserio\Contracts\View\Engine
    {
        $engine = explode('|', $path);
        $path = isset($engine[1]) ? $engine[1] : $path;

        if (! $extension = $this->getExtension($path)) {
            throw new InvalidArgumentException(sprintf('Unrecognized extension in file: [%s]', $path));
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
    public function share(string $key, $value = null)
    {
        if (! is_array($key)) {
            return $this->shared[$key] = $value;
        }

        foreach ($key as $innerKey => $innerValue) {
            $this->share($innerKey, $innerValue);
        }
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
    public function addNamespace(string $namespace, $hints)
    {
        $this->finder->addNamespace($namespace, $hints);
    }

    /**
     * Prepend a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function prependNamespace(string $namespace, $hints)
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
    public function addExtension(string $extension, string $engine, \Closure $resolver = null)
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
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get the engine resolver instance.
     *
     * @return \Viserio\View\Engines\EngineResolver
     */
    public function getEngineResolver(): EngineResolver
    {
        return $this->engines;
    }

    /**
     * Get the view finder instance.
     *
     * @return \Viserio\Contracts\View\Finder
     */
    public function getFinder(): FinderContract
    {
        return $this->finder;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Viserio\Contracts\Events\Dispatcher
     */
    public function getDispatcher(): DispatcherContract
    {
        return $this->events;
    }

    /**
     * Set virtuoso.
     *
     * @param Virtuoso $virtuoso
     */
    public function setVirtuoso(Virtuoso $virtuoso)
    {
        $this->virtuoso = $virtuoso;

        $this->share('__virtuoso', $virtuoso);

        return $this;
    }

    /**
     * Get virtuoso.
     *
     * @return Virtuoso
     */
    public function getVirtuoso(): Virtuoso
    {
        return $this->virtuoso;
    }

    /**
     * Get an item from the shared data.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function shared(string $key, $default = null)
    {
        return Arr::get($this->shared, $key, $default);
    }

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * Get all of the registered named views in environment.
     *
     * @return array
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function parseData($data): array
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
    protected function getExtension(string $path): string
    {
        $extensions = array_keys($this->extensions);

        return Arr::first($extensions, function ($key, $value) use ($path) {
            return Str::endsWith($path, $value);
        });
    }

    /**
     * Get the right view object.
     *
     * @param \Viserio\Contracts\View\Factory            $factory
     * @param \Viserio\Contracts\View\Engine             $engine
     * @param string                                     $view
     * @param string                                     $path
     * @param array|\Viserio\Contracts\Support\Arrayable $data
     *
     * @return \Viserio\View\View|\Viserio\View\VirtuosoView
     */
    protected function getView(FactoryContract $factory, EngineContract $engine, string $view, string $path, $data = [])
    {
        if ($this->virtuoso !== null) {
            $this->virtuoso->callCreator($view = new VirtuosoView($factory, $engine, $view, $path, $data));

            return $view;
        }

        return new View($factory, $engine, $view, $path, $data);
    }
}
