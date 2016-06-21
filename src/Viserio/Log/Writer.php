<?php
namespace Viserio\Log;

use Closure;
use DateTime;
use JsonSerializable;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use RuntimeException;
use Viserio\Contracts\{
    Events\Dispatcher as DispatcherContract,
    Log\Log as LogContract,
    Support\Arrayable,
    Support\Jsonable
};
use Viserio\Log\Traits\ParseLevelTrait;

class Writer implements LogContract
{
    use ParseLevelTrait;

    /**
     * The Monolog logger instance.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;

    /**
     * The event dispatcher instance.
     *
     * @var DispatcherContract
     */
    protected $dispatcher;

    /**
     * The handler parser instance.
     *
     * @var HandlerParser
     */
    protected $handlerParser;

    /**
     * Create a new log writer instance.
     *
     * @param \Monolog\Logger    $monolog
     * @param \Viserio\Contracts\Events\Dispatcher $dispatcher
     */
    public function __construct(MonologLogger $monolog, DispatcherContract $dispatcher = null)
    {
        // PSR 3 log message formatting for all handlers
        $monolog->pushProcessor(new PsrLogMessageProcessor());

        $this->handlerParser = new HandlerParser($monolog);

        $this->monolog = $this->handlerParser->getMonolog();
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function useFiles(
        string $path,
        string $level = 'debug',
        $processor = null,
        $formatter = null
    ) {
        $this->handlerParser->parseHandler(
            'stream',
            $path,
            $level,
            $processor,
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
        $processor = null,
        $formatter = null
    ) {
        $this->handlerParser->parseHandler(
            new RotatingFileHandler($path, $days, $this->parseLevel($level)),
            '',
            '',
            $processor,
            $formatter
        );
    }

    /**
     * Log an emergency message to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function emergency($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an alert message to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function alert($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function critical($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an error message to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function error($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function warning($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a notice to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function notice($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function info($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param string $message
     * @param array  $context
     */
    public function debug($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        return $this->writeLog($level, $message, $context);
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return PsrLoggerInterface
     */
    public function getMonolog(): PsrLoggerInterface
    {
        return $this->monolog;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param \Viserio\Contracts\Events\Dispatcher $dispatcher
     *
     * @return void
     */
    public function setEventDispatcher(DispatcherContract $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

   /**
     * Get the event dispatcher instance.
     *
     * @return \Viserio\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher(): DispatcherContract
    {
        if ($this->dispatcher === null) {
            throw new RuntimeException('Events dispatcher has not been set.');
        }

        return $this->dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function on(Closure $callback)
    {
        $this->getEventDispatcher()->on('viserio.log', $callback);
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
        return call_user_func_array([$this->monolog, $method], $parameters);
    }

    /**
     * Emit a log event.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function emitLogEvent(string $level, string $message, array $context = [])
    {
        // If the event dispatcher is set, we will pass along the parameters to the
        // log listeners. These are useful for building profilers or other tools
        // that aggregate all of the log messages for a given "request" cycle.
        $this->getEventDispatcher()->emit('viserio.log', compact('level', 'message', 'context'));
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return mixed
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        } elseif ($message instanceof Jsonable) {
            return $message->toJson();
        } elseif ($message instanceof Arrayable) {
            return var_export($message->toArray(), true);
        } elseif ($message instanceof JsonSerializable) {
            return var_export($message->jsonSerialize(), true);
        }

        return $message;
    }

    /**
     * Write a message to Monolog.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    protected function writeLog($level, $message, $context)
    {
        if ($this->dispatcher !== null) {
            $this->emitLogEvent($level, $message = $this->formatMessage($message), $context);
        }

        $this->monolog->{$level}($message, $context);
    }
}
