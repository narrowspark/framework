<?php
declare(strict_types=1);
namespace Viserio\Component\View;

use Closure;
use InvalidArgumentException;
use Narrowspark\Arr\Arr;
use Viserio\Component\Contracts\Support\Arrayable;
use Viserio\Component\Contracts\View\Engine as EngineContract;
use Viserio\Component\Contracts\View\EngineResolver as EngineResolverContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Contracts\View\View as ViewContract;
use Viserio\Component\View\Traits\NormalizeNameTrait;

class Factory implements FactoryContract
{
    use NormalizeNameTrait;

    /**
     * The engines instance.
     *
     * @var \Viserio\Component\Contracts\View\EngineResolver
     */
    protected $engines;

    /**
     * The view finder implementation.
     *
     * @var \Viserio\Component\Contracts\View\Finder
     */
    protected $finder;

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
        'php'   => 'php',
        'phtml' => 'php',
        'css'   => 'file',
        'js'    => 'file',
        'md'    => 'markdown',
    ];

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Create a new factory instance.
     *
     * @param \Viserio\Component\Contracts\View\EngineResolver $engines
     * @param \Viserio\Component\Contracts\View\Finder         $finder
     */
    public function __construct(
        EngineResolverContract $engines,
        FinderContract $finder
    ) {
        $this->engines = $engines;
        $this->finder  = $finder;

        $this->share('__env', $this);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $view): bool
    {
        try {
            $this->getFinder()->find($view);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function file(string $path, array $data = [], array $mergeData = []): ViewContract
    {
        $data   = \array_merge($mergeData, $this->parseData($data));
        $engine = $this->getEngineFromPath($path);

        return $this->getView($this, $engine, $path, ['path' => $path], $data);
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $view, array $data = [], array $mergeData = []): ViewContract
    {
        if (isset($this->aliases[$view])) {
            $view = $this->aliases[$view];
        }

        $view     = $this->normalizeName($view);
        $fileInfo = $this->getFinder()->find($view);

        return $this->getView(
            $this,
            $this->getEngineFromPath($fileInfo['path']),
            $view,
            $fileInfo,
            \array_merge($mergeData, $this->parseData($data))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function of(string $view, array $data = []): ViewContract
    {
        return $this->create($this->names[$view], $data);
    }

    /**
     * {@inheritdoc}
     */
    public function name(string $view, string $name): FactoryContract
    {
        $this->names[$name] = $view;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function alias(string $view, string $alias): FactoryContract
    {
        $this->aliases[$alias] = $view;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function renderEach(string $view, array $data, string $iterator, string $empty = 'raw|'): string
    {
        $result = '';

        // If is actually data in the array, we will loop through the data and append
        // an instance of the partial view to the final result HTML passing in the
        // iterated value of this data array, allowing the views to access them.
        if (\count($data) > 0) {
            foreach ($data as $key => $value) {
                $data = ['key' => $key, $iterator => $value];
                $result .= $this->create($view, $data)->render();
            }
        } else {
            // If there is no data in the array, we will render the contents of the empty view.
            $result = $this->create($empty)->render();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEngineFromPath(string $path): EngineContract
    {
        $engine = \explode('|', $path);
        $path   = $engine[1] ?? $path;

        if (! $extension = $this->getExtension($path)) {
            throw new InvalidArgumentException(\sprintf('Unrecognized extension in file: [%s]', $path));
        }

        return $this->engines->resolve($this->extensions[$extension]);
    }

    /**
     * {@inheritdoc}
     */
    public function share($key, $value = null)
    {
        $keys = \is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->shared[$key] = $value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function addLocation(string $location): FactoryContract
    {
        $this->getFinder()->addLocation($location);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function addNamespace(string $namespace, $hints): FactoryContract
    {
        $this->getFinder()->addNamespace($namespace, $hints);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function replaceNamespace(string $namespace, $hints): FactoryContract
    {
        $this->getFinder()->replaceNamespace($namespace, $hints);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function prependNamespace(string $namespace, $hints): FactoryContract
    {
        $this->getFinder()->prependNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Flush the cache of views located by the finder.
     */
    public function flushFinderCache(): void
    {
        $this->getFinder()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function addExtension(string $extension, string $engine, Closure $resolver = null): FactoryContract
    {
        $this->getFinder()->addExtension($extension);

        if (isset($resolver)) {
            $this->engines->register($engine, $resolver);
        }

        if (isset($this->extensions[$extension])) {
            unset($this->extensions[$extension]);
        }

        $this->extensions = \array_merge([$extension => $engine], $this->extensions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getEngineResolver(): EngineResolverContract
    {
        return $this->engines;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinder(): FinderContract
    {
        return $this->finder;
    }

    /**
     * {@inheritdoc}
     */
    public function shared(string $key, $default = null)
    {
        return $this->shared[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * {@inheritdoc}
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
     * @return null|string
     */
    protected function getExtension(string $path): ?string
    {
        $callback = function ($value) use ($path) {
            return $this->endsWith($path, $value);
        };

        foreach (\array_keys($this->extensions) as $key => $value) {
            if ($callback($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get the right view object.
     *
     * @param \Viserio\Component\Contracts\View\Factory            $factory
     * @param \Viserio\Component\Contracts\View\Engine             $engine
     * @param string                                               $view
     * @param array                                                $fileInfo
     * @param array|\Viserio\Component\Contracts\Support\Arrayable $data
     *
     * @return \Viserio\Component\View\View
     */
    protected function getView(
        FactoryContract $factory,
        EngineContract $engine,
        string $view,
        array $fileInfo,
        $data = []
    ) {
        return new View($factory, $engine, $view, $fileInfo, $data);
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private function endsWith(string $haystack, string $needle): bool
    {
        $length = \mb_strlen($needle);

        if ($length == 0) {
            return true;
        }

        return \mb_substr($haystack, -$length) === $needle;
    }
}
