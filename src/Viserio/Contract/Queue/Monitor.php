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

namespace Viserio\Contract\Queue;

interface Monitor
{
    /**
     * Register a callback to be executed when a job fails after the maximum amount of retries.
     */
    public function failing($callback);

    /**
     * Register a callback to be executed when a daemon queue is stopping.
     */
    public function stopping($callback);

    /**
     * Register an event listener for the exception occurred job event.
     */
    public function exceptionOccurred($callback): void;
}
