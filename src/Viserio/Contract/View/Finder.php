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

interface Finder
{
    /**
     * Hint path delimiter value.
     *
     * @var string
     */
    public const HINT_PATH_DELIMITER = '::';

    /**
     * Get the fully qualified location of the view.
     *
     * @param string $view
     *
     * @return array
     */
    public function find(string $view): array;

    /**
     * Add a location to the finder.
     *
     * @param string $location
     *
     * @return self
     */
    public function addLocation(string $location): self;

    /**
     * Prepend a location to the finder.
     *
     * @param string $location
     *
     * @return void
     */
    public function prependLocation(string $location): void;

    /**
     * Add a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param array|string $hints
     *
     * @return self
     */
    public function addNamespace(string $namespace, $hints): self;

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param array|string $hints
     *
     * @return self
     */
    public function prependNamespace(string $namespace, $hints): self;

    /**
     * Register an extension with the view finder.
     *
     * @param string $extension
     *
     * @return self
     */
    public function addExtension(string $extension): self;

    /**
     * Returns whether or not the view specify a hint information.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHintInformation(string $name): bool;

    /**
     * Get the active view paths.
     *
     * @return array
     */
    public function getPaths(): array;

    /**
     * Set the active view paths.
     *
     * @param string[] $paths
     *
     * @return self
     */
    public function setPaths(array $paths): self;

    /**
     * Get the namespace to file path hints.
     *
     * @return array
     */
    public function getHints(): array;

    /**
     * Get registered extensions.
     *
     * @return array
     */
    public function getExtensions(): array;

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
     * Flush the cache of located views.
     *
     * @return void
     */
    public function reset(): void;
}
