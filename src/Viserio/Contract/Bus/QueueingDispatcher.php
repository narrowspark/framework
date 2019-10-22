<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Bus;

use Closure;

interface QueueingDispatcher
{
    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param mixed         $command
     * @param null|\Closure $afterResolving
     *
     * @return mixed
     */
    public function dispatch($command, Closure $afterResolving = null);

    /**
     * Dispatch a command to its appropriate handler behind a queue.
     *
     * @param mixed $command
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function dispatchToQueue($command);
}
