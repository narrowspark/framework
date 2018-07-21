<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parser;

interface Loader
{
    /**
     * Set directories.
     *
     * @param array $directories
     *
     * @return \Viserio\Component\Contract\Parser\Loader
     */
    public function setDirectories(array $directories): self;

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
     * @return \Viserio\Component\Contract\Parser\Loader
     */
    public function addDirectory(string $directory): self;

    /**
     * Load the given file path.
     *
     * @param string     $file
     * @param null|array $options
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\RuntimeException      if wrong options are given
     * @throws \Viserio\Component\Contract\Parser\Exception\FileNotFoundException
     *
     * @return array
     */
    public function load(string $file, array $options = null): array;

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\FileNotFoundException
     *
     * @return string
     */
    public function exists(string $file): string;
}
