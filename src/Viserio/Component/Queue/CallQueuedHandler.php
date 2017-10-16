<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Viserio\Component\Contract\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contract\Queue\Job as JobContract;

class CallQueuedHandler
{
    /**
     * The bus dispatcher implementation.
     *
     * @var \Viserio\Component\Contract\Bus\QueueingDispatcher
     */
    protected $dispatcher;

    /**
     * The encrypter implementation.
     *
     * @var \Viserio\Component\Contract\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create a new handler instance.
     *
     * @param \Viserio\Component\Contract\Bus\QueueingDispatcher $dispatcher
     * @param \Viserio\Component\Contract\Encryption\Encrypter   $encrypter
     */
    public function __construct(QueueingDispatcherContract $dispatcher, EncrypterContract $encrypter)
    {
        $this->dispatcher = $dispatcher;
        $this->encrypter  = $encrypter;
    }

    /**
     * Handle the queued job.
     *
     * @param \Viserio\Component\Contract\Queue\Job $job
     * @param array                                 $data
     */
    public function call(JobContract $job, array $data): void
    {
        $command = $this->setJobInstanceIfNecessary(
            $job,
            \unserialize(
                $this->encrypter->decrypt(
                    \array_key_exists('command64', $data) ? $data['command64'] : $data['command']
                )
            )
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
     */
    public function failed(array $data): void
    {
        $command = \unserialize(
            $this->encrypter->decrypt(
                \array_key_exists('command64', $data) ? $data['command64'] : $data['command']
            )
        );

        if (\method_exists($command, 'failed')) {
            $command->failed();
        }
    }

    /**
     * Set the job instance of the given class if necessary.
     *
     * @param \Viserio\Component\Contract\Queue\Job $job
     * @param mixed                                 $instance
     *
     * @return null|array|bool|float|int|object|resource|string
     */
    protected function setJobInstanceIfNecessary(JobContract $job, $instance)
    {
        if (\is_object($instance) && \is_subclass_of($instance, AbstractInteractsWithQueue::class)) {
            $instance->setJob($job);
        }

        return $instance;
    }
}
