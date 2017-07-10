<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Foundation;

use Closure;

interface Environment
{
    /**
     * Detect the application's current environment.
     *
     * @param \Closure   $callback
     * @param null|array $consoleArgs
     *
     * @return string
     */
    public function detect(Closure $callback, array $consoleArgs = null): string;

    /**
     * Returns true when the runtime used is HHVM or
     * the runtime used is PHP + Xdebug.
     *
     * @return bool
     */
    public function canCollectCodeCoverage(): bool;

    /**
     * Returns the running php/HHVM version.
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return bool
     */
    public function hasXdebug(): bool;

    /**
     * Returns true when the runtime used is PHP.
     *
     * @return bool
     */
    public function isPHP(): bool;

    /**
     * Returns true when the runtime used is Console.
     *
     * @return bool
     */
    public function runningInConsole(): bool;
}
