<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parsers;

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
     * @param string     $file
     * @param null|array $options
     *
     * @throws \Viserio\Component\Contract\Parsers\Exception\RuntimeException      if wrong options are given
     * @throws \Viserio\Component\Contract\Parsers\Exception\FileNotFoundException
     *
     * @return array
     */
    public function load(string $file, array $options = null): array;

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @throws \Viserio\Component\Contract\Parsers\Exception\FileNotFoundException
     *
     * @return string
     */
    public function exists(string $file): string;
}
