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
 * Compiler.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Compiler
{
    /**
     * Get the path to the compiled version of a view.
     *
     * @param string $path
     *
     * @return string
     */
    public function getCompiledPath($path);

    /**
     * Determine if the given view is expired.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isExpired($path);

    /**
     * Compile the view at the given path.
     *
     * @param string $path
     */
    public function compile($path);
}
