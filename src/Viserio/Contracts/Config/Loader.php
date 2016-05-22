<?php
namespace Viserio\Contracts\Config;

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
    public function load($file, $tag = null);

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @return bool
     */
    public function exists($file);
}
