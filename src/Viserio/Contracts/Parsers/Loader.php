<?php

declare(strict_types=1);
namespace Viserio\Contracts\Parsers;

interface Loader
{
    /**
     * Set directories
     *
     * @param array $directories
     *
     * @return $this
     */
    public function setDirectories(array $directories): Loader;

    /**
     * Get directories.
     *
     * @return array
     */
    public function getDirectories(): array;

    /**
     * Add directory.
     *
     * @param string $directory
     *
     * @return $this
     */
    public function addDirectory(string $directory): Loader;

    /**
     * Load the given file path.
     *
     * @param string      $file
     * @param string|null $tag
     *
     * @return array
     */
    public function load(string $file, string $tag = null): array;

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @return string|bool
     */
    public function exists(string $file);
}
