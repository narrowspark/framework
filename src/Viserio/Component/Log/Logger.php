<?php
declare(strict_types=1);
namespace Viserio\Component\Log;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Support\Arrayable;
use Viserio\Component\Contract\Support\Jsonable;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Traits\ParseLevelTrait;

class Logger extends LogLevel implements PsrLoggerInterface
{
    use ParseLevelTrait;
    use EventManagerAwareTrait;
    use LoggerTrait;

    /**
     * The MESSAGE event allows you building profilers or other tools
     * that aggregate all of the log messages for a given "request" cycle.
     *
     * @var string
     */
    public const MESSAGE = 'log.message';

    /**
     * The handler parser instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new log writer instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Call Monolog with the given method and parameters.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return \call_user_func_array([$this->getMonolog(), $method], $parameters);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param mixed $message
     * @param array $context
     *
     * @throws \Psr\Log\InvalidArgumentException
     *
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $message = $this->formatMessage($message);

        if ($this->eventManager !== null) {
            // If the event dispatcher is set, we will pass along the parameters to the
            // log listeners. These are useful for building profilers or other tools
            // that aggregate all of the log messages for a given "request" cycle.
            $this->eventManager->trigger(
                new MessageLoggedEvent($this, $level, $message, $context)
            );
        }

        if (! \method_exists($this->getMonolog(), $level)) {
            throw new InvalidArgumentException(\sprintf(
                'Call to undefined method \Monolog\Logger::%s',
                $level
            ));
        }

        $this->getMonolog()->{$level}($message, $context);
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getMonolog(): PsrLoggerInterface
    {
        return $this->logger;
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return null|bool|float|int|object|string
     */
    private function formatMessage($message)
    {
        if (\is_array($message)) {
            return \var_export($message, true);
        }

        // @codeCoverageIgnoreStart
        if ($message instanceof Jsonable) {
            return $message->toJson();
        }

        if ($message instanceof Arrayable) {
            return \var_export($message->toArray(), true);
        }
        // @codeCoverageIgnoreEnd

        return $message;
    }
}
