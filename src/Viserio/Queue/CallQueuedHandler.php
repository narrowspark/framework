<?php
declare(strict_types=1);
namespace Viserio\Queue;

use Viserio\Contracts\{
    Bus\QueueingDispatcher as QueueingDispatcherContract,
    Encryption\Encrypter as EncrypterContract,
    Queue\Job as JobContract
};
use Viserio\Queue\AbstractInteractsWithQueue;

class CallQueuedHandler
{
    /**
     * The bus dispatcher implementation.
     *
     * @var \Viserio\Contracts\Bus\QueueingDispatcher
     */
    protected $dispatcher;

    /**
     * The encrypter implementation.
     *
     * @var \Viserio\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create a new handler instance.
     *
     * @param \Viserio\Contracts\Bus\QueueingDispatcher $dispatcher
     * @param \Viserio\Contracts\Encryption\Encrypter   $encrypter
     */
    public function __construct(QueueingDispatcherContract $dispatcher, EncrypterContract $encrypter)
    {
        $this->dispatcher = $dispatcher;
        $this->encrypter = $encrypter;
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
        $command = $this->setJobInstanceIfNecessary(
            $job,
            unserialize($this->encrypter->decrypt($data['command']))
        );

        $this->dispatcher->dispatch($command);

        if (! $job->isDeletedOrReleased()) {
            $job->delete();
        }
    }

    /**
     * Call the failed method on the job instance.
     *
     * @param array $data
     *
     * @return void
     */
    public function failed(array $data)
    {
        $command = unserialize($this->encrypter->decrypt($data['command']));

        if (method_exists($command, 'failed')) {
            $command->failed();
        }
    }

    /**
     * Set the job instance of the given class if necessary.
     *
     * @param \Viserio\Contracts\Queue\Job $job
     * @param mixed                        $instance
     *
     * @return integer|double|string|null|array|boolean|resource|object
     */
    protected function setJobInstanceIfNecessary(JobContract $job, $instance)
    {
        if (is_object($instance) && is_subclass_of($instance, AbstractInteractsWithQueue::class)) {
            $instance->setJob($job);
        }

        return $instance;
    }
}
