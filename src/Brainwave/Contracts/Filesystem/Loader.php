<?php
namespace Brainwave\Contracts\Filesystem;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

/**
 * Loader.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
interface Loader
{
    /**
     * Load the given configuration group.
     *
     * @param string      $file
     * @param string|null $group
     * @param string|null $environment
     * @param string|null $namespace
     *
     * @return array
     */
    public function load($file, $group = null, $environment = null, $namespace = null);

    /**
     * Determine if the given file exists.
     *
     * @param string      $file
     * @param string|null $group
     * @param string|null $namespace
     * @param string|null $environment
     *
     * @return bool|array
     */
    public function exists($file, $group = null, $environment = null, $namespace = null);

    /**
     * Apply any cascades to an array of package options.
     *
     * @param string      $file
     * @param string|null $packages
     * @param string|null $group
     * @param string|null $env
     * @param array|null  $items
     * @param string      $namespace
     *
     * @return array
     */
    public function cascadePackage(
        $file,
        $packages = null,
        $group = null,
        $env = null,
        $items = null,
        $namespace = 'packages'
    );
}
