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

namespace Viserio\Contract\Mail;

use Closure;
use Viserio\Contract\Queue\Job as JobContract;
use Viserio\Contract\Queue\QueueConnector as QueueConnectorContract;

interface QueueMailer extends Mailer
{
    /**
     * Get the queue manager instance.
     */
    public function getQueue(): QueueConnectorContract;

    /**
     * Queue a new e-mail message for sending.
     *
     * @param array|string   $view
     * @param Closure|string $callback
     */
    public function queue($view, array $data = [], $callback = null, ?string $queue = null);

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * @param array|string   $view
     * @param Closure|string $callback
     */
    public function onQueue(string $queue, $view, array $data, $callback);

    /**
     * Queue a new e-mail message for sending after (n) seconds.
     *
     * @param array|string   $view
     * @param Closure|string $callback
     */
    public function later(
        int $delay,
        $view,
        array $data = [],
        $callback = null,
        ?string $queue = null
    );

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
     *
     * @param array|string   $view
     * @param Closure|string $callback
     */
    public function laterOn(
        string $queue,
        int $delay,
        $view,
        array $data,
        $callback
    );

    /**
     * Handle a queued e-mail message job.
     */
    public function handleQueuedMessage(JobContract $job, array $data);
}
