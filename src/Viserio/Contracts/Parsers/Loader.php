<?php
namespace Viserio\Contracts\Parsers;

interface Loader
{
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
