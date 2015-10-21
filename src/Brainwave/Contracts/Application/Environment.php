<?php

namespace Brainwave\Contracts\Application;

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
 * Environment.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Environment
{
    /**
     * Detect the application's current environment.
     *
     * @param \Closure   $callback
     * @param array|null $consoleArgs
     *
     * @return string
     */
    public function detect(\Closure $callback, $consoleArgs = null);

    /**
     * Returns true when the runtime used is HHVM or
     * the runtime used is PHP + Xdebug.
     *
     * @return bool
     */
    public function canCollectCodeCoverage();

    /**
     * Returns the running php/HHVM version.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return bool
     */
    public function hasXdebug();

    /**
     * Returns true when the runtime used is HHVM.
     *
     * @return bool
     */
    public function isHHVM();

    /**
     * Returns true when the runtime used is PHP.
     *
     * @return bool
     */
    public function isPHP();

    /**
     * Returns true when the runtime used is Console.
     *
     * @return bool
     */
    public function runningInConsole();
}
