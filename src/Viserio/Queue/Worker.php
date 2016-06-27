<?php
namespace Viserio\Queue;

use Viserio\Contracts\{
    Events\Dispatcher as DispatcherContract,
    Queue\FailedJobProvider as FailedJobProviderContract
};

class Worker
{
    /**
     * The queue manager instance.
     *
     * @var \Viserio\Queue\QueueManager
     */
    protected $manager;

    /**
     * The failed job provider implementation.
     *
     * @var \Viserio\Contracts\Queue\FailedJobProvider
     */
    protected $failer;

    /**
     * The event dispatcher instance.
     *
     * @var \Viserio\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new queue worker.
     *
     * @param \Viserio\Queue\QueueManager                $manager
     * @param \Viserio\Contracts\Queue\FailedJobProvider $failer
     * @param \Viserio\Contracts\Events\Dispatcher       $events
     */
    public function __construct(
        QueueManager $manager,
        FailedJobProviderContract $failer = null,
        DispatcherContract $events = null
    ) {
        $this->failer = $failer;
        $this->events = $events;
        $this->manager = $manager;
    }

    /**
     * Get the queue manager instance.
     *
     * @return \Viserio\Queue\QueueManager
     */
    public function getManager(): QueueManager
    {
        return $this->manager;
    }

    /**
     * Set the queue manager instance.
     *
     * @param \Viserio\Queue\QueueManager $manager
     *
     * @return self
     */
    public function setManager(QueueManager $manager)
    {
        $this->manager = $manager;

        return $this;
    }
}
