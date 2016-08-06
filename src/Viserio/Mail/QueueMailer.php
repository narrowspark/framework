<?php
declare(strict_types=1);
namespace Viserio\Mail;

use Closure;
use InvalidArgumentException;
use SuperClosure\Serializer;
use Swift_Mailer;
use Viserio\Contracts\{
    Container\Traits\ContainerAwareTrait,
    Mail\QueueMailer as QueueMailerContract,
    Queue\Queue as QueueContract,
    Queue\Job as JobContract,
    View\Factory as ViewFactoryContract
};
use Viserio\Support\{
    Invoker,
    Str
};

class QueueMailer extends Mailer implements QueueMailerContract
{
    use ContainerAwareTrait;

    /**
     * The super closure serializer instance.
     *
     * @var \SuperClosure\Serializer|null
     */
    protected $serializer;

    /**
     * Create a new Mailer instance.
     *
     * @param \Swift_Mailer                   $swift
     * @param \Viserio\Contracts\View\Factory $views
     * @param \Viserio\Contracts\Queue\Queue  $queue
     * @param \SuperClosure\Serializer        $serializer
     */
    public function __construct(
        Swift_Mailer $swift,
        ViewFactoryContract $views,
        QueueContract $queue,
        Serializer $serializer
    ) {
        $this->swift = $swift;
        $this->views = $views;
        $this->queue = $queue;
        $this->serializer = $serializer;
    }

    /**
     * Set super closure serializer instance.
     *
     * @param \SuperClosure\Serializer $serializer
     *
     * @return $this
     */
    public function setSerializer(Serializer $serializer): QueueMailerContract
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Get super closure serializer instance.
     *
     * @return \SuperClosure\Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
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

        return $this->serializer->serialize($callback);
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
        if (Str::contains($data['callback'], 'SerializableClosure')) {
            return $this->serializer->unserialize($data['callback']);
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
