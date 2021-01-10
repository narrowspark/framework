<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\View;

use InvalidArgumentException;
use Viserio\Contract\View\Engine as EngineContract;
use Viserio\Contract\View\EngineResolver as EngineResolverContract;

interface Factory
{
    /**
     * Determine if a given view exists.
     */
    public function exists(string $view): bool;

    /**
     * Get the evaluated view contents for the given path.
     *
     * @return \Viserio\Contract\View\View
     */
    public function file(string $path, array $data = [], array $mergeData = []): View;

    /**
     * Get the evaluated view contents for the given view.
     *
     * @return \Viserio\Contract\View\View
     */
    public function create(string $view, array $data = [], array $mergeData = []): View;

    /**
     * Get the evaluated view contents for a named view.
     *
     * @param string[] $data
     *
     * @return \Viserio\Contract\View\View
     */
    public function of(string $view, array $data = []): View;

    /**
     * Register a named view.
     */
    public function name(string $view, string $name): self;

    /**
     * Add an alias for a view.
     */
    public function alias(string $view, string $alias): self;

    /**
     * Get the rendered contents of a partial from a loop.
     */
    public function renderEach(string $view, array $data, string $iterator, string $empty = 'raw|'): string;

    /**
     * Get the appropriate view engine for the given path.
     *
     * @throws InvalidArgumentException
     *
     * @return \Viserio\Contract\View\Engine
     */
    public function getEngineFromPath(string $path): Engine;

    /**
     * Add a piece of shared data to the environment.
     *
     * @param array|string $key
     */
    public function share($key, $value = null);

    /**
     * Add a location to the array of view locations.
     */
    public function addLocation(string $location): self;

    /**
     * Add a new namespace to the loader.
     *
     * @param array|string $hints
     */
    public function addNamespace(string $namespace, $hints): self;

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param array|string $hints
     */
    public function replaceNamespace(string $namespace, $hints): self;

    /**
     * Prepend a new namespace to the loader.
     *
     * @param array|string $hints
     */
    public function prependNamespace(string $namespace, $hints): self;

    /**
     * Register a valid view extension and its engine.
     *
     * @param \Viserio\Contract\View\Engine $engine
     */
    public function addExtension(string $extension, string $engineName, ?EngineContract $engine = null): self;

    /**
     * Get the extension to engine bindings.
     */
    public function getExtensions(): array;

    /**
     * Get the engine resolver instance.
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
     */
    public function shared(string $key, $default = null);

    /**
     * Get all of the shared data for the environment.
     */
    public function getShared(): array;

    /**
     * Get all of the registered named views in environment.
     */
    public function getNames(): array;
}
