<?php

declare(strict_types=1);
namespace Viserio\Contracts\View;

use Closure;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;

interface Factory
{
    /**
     * Determine if a given view exists.
     *
     * @param string $view
     *
     * @return bool
     */
    public function exists(string $view): bool;

    /**
     * Get the evaluated view contents for the given path.
     *
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Viserio\View\View
     */
    public function file(string $path, array $data = [], array $mergeData = []): View;

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Viserio\View\View
     */
    public function create(string $view, array $data = [], array $mergeData = []): View;

    /**
     * Get the evaluated view contents for a named view.
     *
     * @param string   $view
     * @param string[] $data
     *
     * @return \Viserio\View\View
     */
    public function of(string $view, array $data = []): View;

    /**
     * Register a named view.
     *
     * @param string $view
     * @param string $name
     *
     * @return $this
     */
    public function name(string $view, string $name): Factory;

    /**
     * Add an alias for a view.
     *
     * @param string $view
     * @param string $alias
     *
     * @return $this
     */
    public function alias(string $view, string $alias): Factory;

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
    public function renderEach(string $view, array $data, string $iterator, string $empty = 'raw|'): string;

    /**
     * Get the appropriate view engine for the given path.
     *
     * @param string $path
     *
     * @throws \InvalidArgumentException
     *
     * @return \Viserio\Contracts\View\Engine
     */
    public function getEngineFromPath(string $path): Engine;

    /**
     * Add a piece of shared data to the environment.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return mixed
     */
    public function share($key, $value = null);

    /**
     * Add a location to the array of view locations.
     *
     * @param string $location
     *
     * @return $this
     */
    public function addLocation(string $location): Factory;

    /**
     * Add a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     *
     * @return $this
     */
    public function addNamespace(string $namespace, $hints): Factory;

    /**
     * Prepend a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     *
     * @return $this
     */
    public function prependNamespace(string $namespace, $hints): Factory;

    /**
     * Register a valid view extension and its engine.
     *
     * @param string        $extension
     * @param string        $engine
     * @param \Closure|null $resolver
     *
     * @return $this
     */
    public function addExtension(string $extension, string $engine, Closure $resolver = null): Factory;

    /**
     * Get the extension to engine bindings.
     *
     * @return array
     */
    public function getExtensions(): array;

    /**
     * Get the engine resolver instance.
     *
     * @return \Viserio\Contracts\View\EngineResolver
     */
    public function getEngineResolver(): EngineResolver;

    /**
     * Get the view finder instance.
     *
     * @return \Viserio\Contracts\View\Finder
     */
    public function getFinder(): Finder;

    /**
     * Get the event dispatcher instance.
     *
     * @return \Viserio\Contracts\Events\Dispatcher
     */
    public function getDispatcher(): DispatcherContract;

    /**
     * Set virtuoso.
     *
     * @param Virtuoso $virtuoso
     *
     * @return $this
     */
    public function setVirtuoso(Virtuoso $virtuoso): Factory;

    /**
     * Get virtuoso.
     *
     * @return Virtuoso
     */
    public function getVirtuoso(): Virtuoso;

    /**
     * Get an item from the shared data.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function shared(string $key, $default = null);

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared(): array;

    /**
     * Get all of the registered named views in environment.
     *
     * @return array
     */
    public function getNames(): array;
}
