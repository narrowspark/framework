<?php
namespace Viserio\Contracts\Filesystem;

interface Loader
{
    /**
     * Load the given configuration group.
     *
     * @param string $file
     *
     * @return array
     */
    public function load($file);

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @return bool|array
     */
    public function exists($file);
}
