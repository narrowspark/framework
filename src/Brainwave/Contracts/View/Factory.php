<?php

namespace Brainwave\Contracts\View;

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
 * @version     0.9.8-dev
 */

/**
 * Factory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
interface Factory
{
    /**
     * Determine if a given view exists.
     *
     * @param string $view
     *
     * @return bool
     */
    public function exists($view);

    /**
     * Get the evaluated view contents for the given path.
     *
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Brainwave\View\View
     */
    public function file($path, $data = [], $mergeData = []);

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Brainwave\View\View
     */
    public function make($view, $data = [], $mergeData = []);

    /**
     * Add a piece of shared data to the environment.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function share($key, $value = null);

    /**
     * Add a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function addNamespace($namespace, $hints);
}
