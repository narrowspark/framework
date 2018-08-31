<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Mail;

use Viserio\Component\Contract\Queue\Job as JobContract;
use Viserio\Component\Contract\Queue\QueueConnector as QueueConnectorContract;

interface QueueMailer extends Mailer
{
    /**
     * Get the queue manager instance.
     *
     * @return \Viserio\Component\Contract\Queue\QueueConnector
     */
    public function getQueue(): QueueConnectorContract;

    /**
     * Queue a new e-mail message for sending.
     *
     * @param array|string    $view
     * @param array           $data
     * @param \Closure|string $callback
     * @param null|string     $queue
     *
     * @return mixed
     */
    public function queue($view, array $data = [], $callback = null, string $queue = null);

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * @param string          $queue
     * @param array|string    $view
     * @param array           $data
     * @param \Closure|string $callback
     *
     * @return mixed
     */
    public function onQueue(string $queue, $view, array $data, $callback);

    /**
     * Queue a new e-mail message for sending after (n) seconds.
     *
     * @param int             $delay
     * @param array|string    $view
     * @param array           $data
     * @param \Closure|string $callback
     * @param null|string     $queue
     *
     * @return mixed
     */
    public function later(
        int $delay,
        $view,
        array $data   = [],
        $callback     = null,
        string $queue = null
    );

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
     *
     * @param string          $queue
     * @param int             $delay
     * @param array|string    $view
     * @param array           $data
     * @param \Closure|string $callback
     *
     * @return mixed
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
     *
     * @param \Viserio\Component\Contract\Queue\Job $job
     * @param array                                 $data
     */
    public function handleQueuedMessage(JobContract $job, array $data);
}
