<?php
namespace Viserio\Contracts\Filesystem;

interface Loader
{
    /**
     * Load the given file path.
     *
     * @param string $file
     * @param string $tag
     *
     * @return array
     */
    public function load($file, $tag = null);

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @return boolean|string
     */
    public function exists($file);
}
