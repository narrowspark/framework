<?php
namespace Viserio\Bus;

use Closure;
use RuntimeException;
use ReflectionClass;
use Interop\Container\ContainerInterface;
use Viserio\Contracts\Queue\{
    Queue as QueueContract,
    ShouldQueue as ShouldQueueContract
};
use Viserio\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;

class QueueingDispatcher extends Dispatcher implements QueueingDispatcherContract
{
    /**
     * The queue resolver callback.
     *
     * @var \Closure|null
     */
    protected $queueResolver;

    /**
     * Create a new queue command dispatcher instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Closure|null                         $queueResolver
     */
    public function __construct(Container $container, Closure $queueResolver = null)
    {
        $this->queueResolver = $queueResolver;

        parent::__construct($container);
    }

    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param mixed         $command
     * @param \Closure|null $afterResolving
     *
     * @return mixed
     */
    public function dispatch($command, Closure $afterResolving = null)
    {
        if ($this->queueResolver && $this->commandShouldBeQueued($command)) {
            return $this->dispatchToQueue($command);
        }

        return parent::dispatch($command, $afterResolving);
    }

     /**
     * Determine if the given command should be queued.
     *
     * @param mixed $command
     *
     * @return bool
     */
    protected function commandShouldBeQueued($command): string
    {
        if ($command instanceof ShouldQueueContract) {
            return true;
        }

        return (new ReflectionClass($this->getHandlerClass($command)))->implementsInterface(ShouldQueue::class);
    }

    /**
     * Dispatch a command to its appropriate handler behind a queue.
     *
     * @param mixed $command
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function dispatchToQueue($command)
    {
        $connection = isset($command->connection) ? $command->connection : null;
        $queue = call_user_func($this->queueResolver, $connection);

        if (!$queue instanceof QueueContract) {
            throw new RuntimeException('Queue resolver did not return a Queue implementation.');
        }

        if (method_exists($command, 'queue')) {
            return $command->queue($queue, $command);
        }

        return $this->pushCommandToQueue($queue, $command);
    }

    /**
     * Push the command onto the given queue instance.
     *
     * @param \Viserio\Contracts\Queue\Queue $queue
     * @param mixed                          $command
     *
     * @return mixed
     */
    protected function pushCommandToQueue(QueueContract $queue, $command)
    {
        if (isset($command->queue, $command->delay)) {
            return $queue->laterOn($command->queue, $command->delay, $command);
        }

        if (isset($command->queue)) {
            return $queue->pushOn($command->queue, $command);
        }

        if (isset($command->delay)) {
            return $queue->later($command->delay, $command);
        }

        return $queue->push($command);
    }
}
