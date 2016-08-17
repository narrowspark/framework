<?php
declare(strict_types=1);
namespace Viserio\Contracts\Mail;

use Viserio\Contracts\Queue\Job as JobContract;
use Viserio\Contracts\Queue\Queue as QueueContract;

interface QueueMailer extends Mailer
{
    /**
     * Set the queue manager instance.
     *
     * @param \Viserio\Contracts\Queue\Queue $queue
     *
     * @return $this
     */
    public function setQueue(QueueContract $queue): QueueMailer;

    /**
     * Get the queue manager instance.
     *
     * @return \Viserio\Contracts\Queue\Queue
     */
    public function getQueue(): QueueContract;

    /**
     * Queue a new e-mail message for sending.
     *
     * @param string|array    $view
     * @param array           $data
     * @param \Closure|string $callback
     * @param string|null     $queue
     *
     * @return mixed
     */
    public function queue($view, array $data = [], $callback = null, string $queue = null);

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * @param string          $queue
     * @param string|array    $view
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
     * @param string|array    $view
     * @param array           $data
     * @param \Closure|string $callback
     * @param string|null     $queue
     *
     * @return mixed
     */
    public function later(
        int $delay,
        $view,
        array $data = [],
        $callback = null,
        string $queue = null
    );

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
     *
     * @param string          $queue
     * @param int             $delay
     * @param string|array    $view
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
     * @param \Viserio\Contracts\Queue\Job $job
     * @param array                        $data
     */
    public function handleQueuedMessage(JobContract $job, array $data);
}
