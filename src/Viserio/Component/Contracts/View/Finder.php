<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\View;

use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;

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
     * @return $this
     */
    public function addLocation(string $location): Finder;

    /**
     * Prepend a location to the finder.
     *
     * @param string $location
     */
    public function prependLocation(string $location);

    /**
     * Add a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param string|array $hints
     *
     * @return $this
     */
    public function addNamespace(string $namespace, $hints): Finder;

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param string|array $hints
     *
     * @return $this
     */
    public function prependNamespace(string $namespace, $hints): Finder;

    /**
     * Register an extension with the view finder.
     *
     * @param string $extension
     *
     * @return $this
     */
    public function addExtension(string $extension): Finder;

    /**
     * Returns whether or not the view specify a hint information.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHintInformation(string $name): bool;

    /**
     * Get the filesystem instance.
     *
     * @return \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    public function getFilesystem(): FilesystemContract;

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
     * @return $this
     */
    public function setPaths(array $paths): Finder;

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
     * @param string|array $hints
     *
     * @return $this
     */
    public function replaceNamespace(string $namespace, $hints): Finder;

    /**
     * Flush the cache of located views.
     */
    public function flush(): void;
}
