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

namespace Viserio\Contract\Exception;

use Throwable;

interface ConsoleHandler extends Handler
{
    /**
     * Register the exception / Error handlers for the application.
     */
    public function register(): void;

    /**
     * Unregister the PHP error handler.
     */
    public function unregister(): void;

    /**
     * Render an exception to the console.
     *
     * @param \Viserio\Contract\Exception\ConsoleOutput $output
     */
    public function render(ConsoleOutput $output, Throwable $exception): void;
}
