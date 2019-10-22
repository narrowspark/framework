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

namespace Viserio\Component\Mail;

use Closure;
use Opis\Closure\SerializableClosure;
use Swift_Mailer;
use Viserio\Contract\Mail\QueueMailer as QueueMailerContract;
use Viserio\Contract\Queue\Job as JobContract;
use Viserio\Contract\Queue\QueueConnector as QueueConnectorContract;

class QueueMailer extends Mailer implements QueueMailerContract
{
    /**
     * Queue instance.
     *
     * @var \Viserio\Contract\Queue\QueueConnector
     */
    protected $queue;

    /**
     * Create a new Mailer instance.
     *
     * @param \Swift_Mailer                          $swiftMailer
     * @param \Viserio\Contract\Queue\QueueConnector $queue
     * @param array                                  $config
     */
    public function __construct(Swift_Mailer $swiftMailer, QueueConnectorContract $queue, array $config)
    {
        parent::__construct($swiftMailer, $config);

        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue(): QueueConnectorContract
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($view, array $data = [], $callback = null, string $queue = null)
    {
        $callback = $this->buildQueueCallable($callback);

        return $this->queue->push(
            'mailer@handleQueuedMessage',
            \compact('view', 'data', 'callback'),
            $queue
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onQueue(string $queue, $view, array $data, $callback)
    {
        return $this->queue($view, $data, $callback, $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function later(
        int $delay,
        $view,
        array $data = [],
        $callback = null,
        string $queue = null
    ) {
        if ($callback !== null) {
            $callback = $this->buildQueueCallable($callback);
        }

        return $this->queue->later(
            $delay,
            'mailer@handleQueuedMessage',
            \compact('view', 'data', 'callback'),
            $queue
        );
    }

    /**
     * {@inheritdoc}
     */
    public function laterOn(
        string $queue,
        int $delay,
        $view,
        array $data,
        $callback
    ) {
        return $this->later($delay, $view, $data, $callback, $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function handleQueuedMessage(JobContract $job, array $data): void
    {
        $this->send($data['view'], $data['data'], $this->getQueuedCallable($data));

        $job->delete();
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param \Closure|string $callback
     *
     * @return \Closure|\Opis\Closure\SerializableClosure|string
     */
    protected function buildQueueCallable($callback)
    {
        if (! $callback instanceof Closure) {
            return $callback;
        }

        return new SerializableClosure($callback);
    }

    /**
     * Get the true callable for a queued e-mail message.
     *
     * @param array $data
     *
     * @return \Closure|string
     */
    protected function getQueuedCallable(array $data)
    {
        if (\strpos($data['callback'], 'SerializableClosure') !== false) {
            return \unserialize($data['callback']);
        }

        return $data['callback'];
    }
}
