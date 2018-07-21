<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\View;

use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;

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
     * @return \Viserio\Component\Contract\View\Finder
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
     * @return \Viserio\Component\Contract\View\Finder
     */
    public function addNamespace(string $namespace, $hints): self;

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param array|string $hints
     *
     * @return \Viserio\Component\Contract\View\Finder
     */
    public function prependNamespace(string $namespace, $hints): self;

    /**
     * Register an extension with the view finder.
     *
     * @param string $extension
     *
     * @return \Viserio\Component\Contract\View\Finder
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
     * Get the filesystem instance.
     *
     * @return \Viserio\Component\Contract\Filesystem\Filesystem
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
     * @return \Viserio\Component\Contract\View\Finder
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
     * @return \Viserio\Component\Contract\View\Finder
     */
    public function replaceNamespace(string $namespace, $hints): self;

    /**
     * Flush the cache of located views.
     *
     * @return void
     */
    public function reset(): void;
}
