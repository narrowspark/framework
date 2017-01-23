<?php
declare(strict_types=1);
namespace Viserio\Component\Mail;

use Closure;
use Interop\Container\ContainerInterface;
use Opis\Closure\SerializableClosure;
use Viserio\Component\Contracts\Mail\QueueMailer as QueueMailerContract;
use Viserio\Component\Contracts\Queue\Job as JobContract;
use Viserio\Component\Contracts\Queue\Queue as QueueContract;

class QueueMailer extends Mailer implements QueueMailerContract
{
    /**
     * Queue instance.
     *
     * @var \Viserio\Component\Contracts\Queue\Queue
     */
    protected $queue;

    /**
     * Create a new Mailer instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->queue = $container->get(QueueContract::class);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setQueue(QueueContract $queue): QueueMailerContract
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getQueue(): QueueContract
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
            compact('view', 'data', 'callback'),
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
            compact('view', 'data', 'callback'),
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
    public function handleQueuedMessage(JobContract $job, array $data)
    {
        $this->send($data['view'], $data['data'], $this->getQueuedCallable($data));

        $job->delete();
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param \Closure|string $callback
     *
     * @return string
     */
    protected function buildQueueCallable($callback): string
    {
        if (! $callback instanceof Closure) {
            return $callback;
        }

        return new SerializableClosure($callback, true);
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
        if (mb_strpos($data['callback'], 'SerializableClosure') !== false) {
            return unserialize($data['callback']);
        }

        return $data['callback'];
    }
}
