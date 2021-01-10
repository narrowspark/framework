<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Foundation;

use Closure;

interface Environment
{
    /**
     * Detect the application's current environment.
     *
     * @return bool|string
     */
    public function detect(Closure $callback, ?array $consoleArgs = null);

    /**
     * Returns true when the runtime used is HHVM or
     * the runtime used is PHP + Xdebug.
     */
    public function canCollectCodeCoverage(): bool;

    /**
     * Returns the running php/HHVM version.
     */
    public function getVersion(): string;

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     */
    public function hasXdebug(): bool;

    /**
     * Returns true when the runtime used is Console.
     */
    public function isRunningInConsole(): bool;
}
