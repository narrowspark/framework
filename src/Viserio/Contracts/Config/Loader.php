<?php
namespace Viserio\Contracts\Config;

interface Loader
{
    /**
     * Load the given configuration group.
     *
     * @param string      $file
     * @param string|null $environment
     * @param string|null $namespace
     *
     * @return array
     */
    public function load($file, $environment = null, $namespace = null);

    /**
     * Determine if the given file exists.
     *
     * @param string      $file
     * @param string|null $namespace
     * @param string|null $environment
     *
     * @return bool|array
     */
    public function exists($file, $environment = null, $namespace = null);
}
