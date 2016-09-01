<?php
declare(strict_types=1);
namespace Viserio\Log;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Log\Log as LogContract;
use Viserio\Contracts\Support\Arrayable;
use Viserio\Contracts\Support\Jsonable;
use Viserio\Log\Traits\ParseLevelTrait;

class Writer implements LogContract
{
    use ParseLevelTrait;
    use EventsAwareTrait;

    /**
     * The Monolog logger instance.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;

    /**
     * The handler parser instance.
     *
     * @var HandlerParser
     */
    protected $handlerParser;

    /**
     * Create a new log writer instance.
     *
     * @param \Monolog\Logger                           $monolog
     * @param \Viserio\Contracts\Events\Dispatcher|null $dispatcher
     */
    public function __construct(MonologLogger $monolog, DispatcherContract $events = null)
    {
        // PSR 3 log message formatting for all handlers
        $monolog->pushProcessor(new PsrLogMessageProcessor());

        $this->handlerParser = new HandlerParser($monolog);

        $this->monolog = $this->handlerParser->getMonolog();
        $this->events = $events;
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
     * @param mixed $message
     * @param array $context
     */
    public function emergency($message, array $context = [])
    {
        return $this->writeLog('emergency', $message, $context);
    }

    /**
     * Log an alert message to the logs.
     *
     * @param mixed $message
     * @param array $context
     */
    public function alert($message, array $context = [])
    {
        return $this->writeLog('alert', $message, $context);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param mixed $message
     * @param array $context
     */
    public function critical($message, array $context = [])
    {
        return $this->writeLog('critical', $message, $context);
    }

    /**
     * Log an error message to the logs.
     *
     * @param mixed $message
     * @param array $context
     */
    public function error($message, array $context = [])
    {
        return $this->writeLog('error', $message, $context);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param mixed $message
     * @param array $context
     */
    public function warning($message, array $context = [])
    {
        return $this->writeLog('warning', $message, $context);
    }

    /**
     * Log a notice to the logs.
     *
     * @param mixed $message
     * @param array $context
     */
    public function notice($message, array $context = [])
    {
        return $this->writeLog('notice', $message, $context);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param mixed $message
     * @param array $context
     */
    public function info($message, array $context = [])
    {
        return $this->writeLog('info', $message, $context);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param mixed $message
     * @param array $context
     */
    public function debug($message, array $context = [])
    {
        return $this->writeLog('debug', $message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param mixed  $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        return $this->writeLog($level, $message, $context);
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return MonologLogger
     */
    public function getMonolog(): MonologLogger
    {
        return $this->monolog;
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return string|object|int|float|null|bool
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        } elseif ($message instanceof Jsonable) {
            return $message->toJson();
        } elseif ($message instanceof Arrayable) {
            return var_export($message->toArray(), true);
        }

        return $message;
    }

    /**
     * Write a message to Monolog.
     *
     * @param string $level
     * @param mixed  $message
     * @param array  $context
     */
    protected function writeLog(string $level, $message, array $context)
    {
        $message = $this->formatMessage($message);

        if ($this->events !== null) {
            // If the event dispatcher is set, we will pass along the parameters to the
            // log listeners. These are useful for building profilers or other tools
            // that aggregate all of the log messages for a given "request" cycle.
            $this->getEventsDispatcher()->emit('viserio.log', compact('level', 'message', 'context'));
        }

        $this->monolog->{$level}($message, $context);
    }
}
