<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers;

interface Loader
{
    /**
     * Set directories.
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
     * @param array|null  $options
     *
     * @throws \RuntimeException                                              If wrong options are given.
     * @throws \Viserio\Component\Contracts\Parsers\Exception\LoadingException
     *
     * @return array
     */
    public function load(string $file, array $options = null): array;

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @return string|bool
     */
    public function exists(string $file);
}
