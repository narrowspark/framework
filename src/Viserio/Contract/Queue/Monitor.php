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

namespace Viserio\Contract\Queue;

interface Monitor
{
    /**
     * Register a callback to be executed when a job fails after the maximum amount of retries.
     *
     * @param mixed $callback
     */
    public function failing($callback);

    /**
     * Register a callback to be executed when a daemon queue is stopping.
     *
     * @param mixed $callback
     */
    public function stopping($callback);

    /**
     * Register an event listener for the exception occurred job event.
     *
     * @param mixed $callback
     *
     * @return void
     */
    public function exceptionOccurred($callback): void;
}
