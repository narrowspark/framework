<?php
declare(strict_types=1);
namespace Viserio\Mail;

use Closure;
use InvalidArgumentException;
use Swift_Mailer;
use Opis\Closure\SerializableClosure;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Mail\QueueMailer as QueueMailerContract;
use Viserio\Contracts\Queue\Job as JobContract;
use Viserio\Contracts\Queue\Queue as QueueContract;
use Viserio\Contracts\View\Factory as ViewFactoryContract;
use Viserio\Support\Invoker;

class QueueMailer extends Mailer implements QueueMailerContract
{
    use ContainerAwareTrait;

    /**
     * Create a new Mailer instance.
     *
     * @param \Swift_Mailer                   $swift
     * @param \Viserio\Contracts\View\Factory $views
     * @param \Viserio\Contracts\Queue\Queue  $queue
     */
    public function __construct(
        Swift_Mailer $swift,
        ViewFactoryContract $views,
        QueueContract $queue
    ) {
        $this->swift = $swift;
        $this->views = $views;
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueue(QueueContract $queue): QueueMailerContract
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * {@inheritdoc}
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
        $callback = $this->buildQueueCallable($callback);

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

        return serialize(new SerializableClosure($callback));
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
        if (strpos($data['callback'], 'SerializableClosure') !== false) {
            return unserialize($data['callback']);
        }

        return $data['callback'];
    }

    /**
     * Call the provided message builder.
     *
     * @param \Closure|string       $callback
     * @param \Viserio\Mail\Message $message
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof Closure) {
            return call_user_func($callback, $message);
        }

        if ($this->container !== null) {
            $invoker = (new Invoker())
                ->injectByTypeHint(true)
                ->injectByParameterName(true)
                ->setContainer($this->container);

            return $invoker->call($callback)->mail($message);
        }

        throw new InvalidArgumentException('Callback is not valid.');
    }
}
