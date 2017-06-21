<?php
declare(strict_types=1);
namespace Viserio\Component\Log;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Log\Log as LogContract;
use Viserio\Component\Contract\Support\Arrayable;
use Viserio\Component\Contract\Support\Jsonable;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Traits\ParseLevelTrait;

class Writer extends LogLevel implements LogContract
{
    use ParseLevelTrait;
    use EventManagerAwareTrait;
    use LoggerTrait;

    /**
     * The handler parser instance.
     *
     * @var \Viserio\Component\Log\HandlerParser
     */
    protected $handlerParser;

    /**
     * Create a new log writer instance.
     *
     * @param \Viserio\Component\Log\HandlerParser $handlerParser
     */
    public function __construct(HandlerParser $handlerParser)
    {
        $this->handlerParser = $handlerParser;
    }

    /**
     * Call Monolog with the given method and parameters.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return \call_user_func_array([$this->getMonolog(), $method], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function useFiles(
        string $path,
        string $level = 'debug',
        $processors = null,
        $formatter = null
    ): void {
        $this->handlerParser->parseHandler(
            'stream',
            $path,
            $level,
            $processors,
            $formatter
        );
    }

    /**
     * {@inheritdoc}
     */
    public function useDailyFiles(
        string $path,
        int $days = 0,
        string $level = 'debug',
        $processors = null,
        $formatter = null
    ): void {
        $this->handlerParser->parseHandler(
            new RotatingFileHandler($path, $days, self::parseLevel($level)),
            '',
            '',
            $processors,
            $formatter
        );
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param mixed $message
     * @param array $context
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
            $this->getEventManager()->trigger(
                new MessageLoggedEvent($this, $level, $message, $context)
            );
        }

        $this->getMonolog()->{$level}($message, $context);
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog(): MonologLogger
    {
        return $this->handlerParser->getMonolog();
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return null|bool|float|int|object|string
     */
    protected function formatMessage($message)
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
