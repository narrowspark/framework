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
     */
    public function find(string $view): array;

    /**
     * Add a location to the finder.
     */
    public function addLocation(string $location): self;

    /**
     * Prepend a location to the finder.
     */
    public function prependLocation(string $location): void;

    /**
     * Add a namespace hint to the finder.
     *
     * @param array|string $hints
     */
    public function addNamespace(string $namespace, $hints): self;

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param array|string $hints
     */
    public function prependNamespace(string $namespace, $hints): self;

    /**
     * Register an extension with the view finder.
     */
    public function addExtension(string $extension): self;

    /**
     * Returns whether or not the view specify a hint information.
     */
    public function hasHintInformation(string $name): bool;

    /**
     * Get the active view paths.
     */
    public function getPaths(): array;

    /**
     * Set the active view paths.
     *
     * @param string[] $paths
     */
    public function setPaths(array $paths): self;

    /**
     * Get the namespace to file path hints.
     */
    public function getHints(): array;

    /**
     * Get registered extensions.
     */
    public function getExtensions(): array;

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param array|string $hints
     */
    public function replaceNamespace(string $namespace, $hints): self;

    /**
     * Flush the cache of located views.
     */
    public function reset(): void;
}
