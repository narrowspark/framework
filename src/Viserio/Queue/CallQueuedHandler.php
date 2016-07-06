<?php
namespace Viserio\Queue;

use Viserio\Contracts\{
    Bus\QueueingDispatcher as QueueingDispatcherContract,
    Queue\Job as JobContract
};

class CallQueuedHandler
{
    /**
     * The bus dispatcher implementation.
     *
     * @var \Viserio\Contracts\Bus\QueueingDispatcher
     */
    protected $dispatcher;

    /**
     * Create a new handler instance.
     *
     * @param \Viserio\Contracts\Bus\QueueingDispatcher $dispatcher
     */
    public function __construct(QueueingDispatcherContract $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the queued job.
     *
     * @param \Viserio\Contracts\Queue\Job $job
     * @param array                        $data
     *
     * @return void
     */
    public function call(JobContract $job, array $data)
    {

    }
}
