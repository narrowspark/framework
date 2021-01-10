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

namespace Viserio\Contract\Bus;

use Closure;
use RuntimeException;

interface QueueingDispatcher
{
    /**
     * Dispatch a command to its appropriate handler.
     */
    public function dispatch($command, ?Closure $afterResolving = null);

    /**
     * Dispatch a command to its appropriate handler behind a queue.
     *
     * @throws RuntimeException
     */
    public function dispatchToQueue($command);
}
