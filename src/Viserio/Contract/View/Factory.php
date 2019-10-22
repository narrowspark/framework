<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\View;

use Viserio\Contract\View\Engine as EngineContract;
use Viserio\Contract\View\EngineResolver as EngineResolverContract;

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
     * @return \Viserio\Contract\View\View
     */
    public function file(string $path, array $data = [], array $mergeData = []): View;

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Viserio\Contract\View\View
     */
    public function create(string $view, array $data = [], array $mergeData = []): View;

    /**
     * Get the evaluated view contents for a named view.
     *
     * @param string   $view
     * @param string[] $data
     *
     * @return \Viserio\Contract\View\View
     */
    public function of(string $view, array $data = []): View;

    /**
     * Register a named view.
     *
     * @param string $view
     * @param string $name
     *
     * @return self
     */
    public function name(string $view, string $name): self;

    /**
     * Add an alias for a view.
     *
     * @param string $view
     * @param string $alias
     *
     * @return self
     */
    public function alias(string $view, string $alias): self;

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
     * @return \Viserio\Contract\View\Engine
     */
    public function getEngineFromPath(string $path): Engine;

    /**
     * Add a piece of shared data to the environment.
     *
     * @param array|string $key
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
     * @return self
     */
    public function addLocation(string $location): self;

    /**
     * Add a new namespace to the loader.
     *
     * @param string       $namespace
     * @param array|string $hints
     *
     * @return self
     */
    public function addNamespace(string $namespace, $hints): self;

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param string       $namespace
     * @param array|string $hints
     *
     * @return self
     */
    public function replaceNamespace(string $namespace, $hints): self;

    /**
     * Prepend a new namespace to the loader.
     *
     * @param string       $namespace
     * @param array|string $hints
     *
     * @return self
     */
    public function prependNamespace(string $namespace, $hints): self;

    /**
     * Register a valid view extension and its engine.
     *
     * @param string                        $extension
     * @param string                        $engineName
     * @param \Viserio\Contract\View\Engine $engine
     *
     * @return self
     */
    public function addExtension(string $extension, string $engineName, EngineContract $engine = null): self;

    /**
     * Get the extension to engine bindings.
     *
     * @return array
     */
    public function getExtensions(): array;

    /**
     * Get the engine resolver instance.
     *
     * @return \Viserio\Contract\View\EngineResolver
     */
    public function getEngineResolver(): EngineResolverContract;

    /**
     * Get the view finder instance.
     *
     * @return \Viserio\Contract\View\Finder
     */
    public function getFinder(): Finder;

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
