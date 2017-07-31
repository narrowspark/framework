<?php
declare(strict_types=1);
namespace Viserio\Component\Bus;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;
use Viserio\Component\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Viserio\Component\Contracts\Queue\QueueConnector as QueueContract;
use Viserio\Component\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class QueueingDispatcher extends Dispatcher implements QueueingDispatcherContract
{
    /**
     * The queue resolver callback.
     *
     * @var null|\Closure
     */
    protected $queueResolver;

    /**
     * Create a new queue command dispatcher instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|\Closure                     $queueResolver
     */
    public function __construct(ContainerInterface $container, Closure $queueResolver = null)
    {
        $this->queueResolver = $queueResolver;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($command, Closure $afterResolving = null)
    {
        if ($this->queueResolver && $this->commandShouldBeQueued($command)) {
            return $this->dispatchToQueue($command);
        }

        return parent::dispatch($command, $afterResolving);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchToQueue($command)
    {
        $connection = isset($command->connection) ? $command->connection : null;
        $queue      = \call_user_func($this->queueResolver, $connection);

        if (! $queue instanceof QueueContract) {
            throw new RuntimeException('Queue resolver did not return a Queue implementation.');
        }

        if (\method_exists($command, 'queue')) {
            return $command->queue($queue, $command);
        }

        return $this->pushCommandToQueue($queue, $command);
    }

    /**
     * Push the command onto the given queue instance.
     *
     * @param \Viserio\Component\Contracts\Queue\QueueConnector $queue
     * @param mixed                                             $command
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

    /**
     * Determine if the given command should be queued.
     *
     * @param mixed $command
     *
     * @return bool
     */
    protected function commandShouldBeQueued($command): bool
    {
        if ($command instanceof ShouldQueueContract) {
            return true;
        }

        $reflection = new ReflectionClass($this->getHandlerClass($command));

        return $reflection->implementsInterface(ShouldQueueContract::class);
    }
}
