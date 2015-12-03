<?php
namespace Viserio\Contracts\Application;

use Closure;

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
    public function detect(Closure $callback, $consoleArgs = null);

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
