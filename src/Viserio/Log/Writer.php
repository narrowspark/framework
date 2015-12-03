<?php
namespace Viserio\Log;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Contracts\Logging\Log as LogContract;
use Viserio\Log\Traits\FormatterTrait;
use Viserio\Log\Traits\HandlerTrait;
use Viserio\Log\Traits\ProcessorTrait;
use Viserio\Log\Traits\PsrLoggerTrait;

class Writer implements LogContract, PsrLoggerInterface
{
    use FormatterTrait, HandlerTrait, ProcessorTrait, PsrLoggerTrait;

    /**
     * The Monolog logger instance.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;

    /**
     * The Events Dispatcher instance.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * The permission mode for for new log files.
     *
     * @var int|null
     */
    protected $filePermission;

    /**
     * Create a new log writer instance.
     *
     * @param \Monolog\Logger                                             $monolog
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(MonologLogger $monolog, EventDispatcherInterface $dispatcher)
    {
        # PSR 3 log message formatting for all handlers
        $monolog->pushProcessor(new PsrLogMessageProcessor());

        $this->monolog = $monolog;

        if (isset($dispatcher)) {
            $this->dispatcher = $dispatcher;
        }
    }

    /**
     * Register a file log handler.
     *
     * @param string      $path
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     */
    public function useFiles($path, $level = 'debug', $processor = null, $formatter = null)
    {
        $this->parseHandler('stream', $path, $level, $processor, $formatter);
    }

    /**
     * Register a daily file log handler.
     *
     * @param string      $path
     * @param int         $days
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     */
    public function useDailyFiles($path, $days = 0, $level = 'debug', $processor = null, $formatter = null)
    {
        $this->parseHandler(
            new RotatingFileHandler($path, $days, $this->parseLevel($level)),
            '',
            '',
            $processor,
            $formatter
        );
    }

    /**
     * Register an error_log handler.
     *
     * @param string      $level
     * @param int         $messageType
     * @param object|null $processor
     * @param object|null $formatter
     */
    public function useErrorLog(
        $level = 'debug',
        $messageType = ErrorLogHandler::OPERATING_SYSTEM,
        $processor = null,
        $formatter = null
    ) {
        $this->parseHandler(
            new ErrorLogHandler($messageType, $this->parseLevel($level)),
            '',
            '',
            $processor,
            $formatter
        );
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog()
    {
        return $this->monolog;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Set the file permission for newly created files
     *
     * @param  int|null  $filePermission
     * @return void
     */
    public function setFilePermission($filePermission)
    {
        $this->filePermission = $filePermission;
    }

    /**
     * Get the file permission for creating files
     *
     * @return int|null
     */
    public function getFilePermission()
    {
        return $this->filePermission;
    }

    /**
     * Call Monolog with the given method and parameters.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    protected function callMonolog($method, $parameters)
    {
        if (is_array($parameters[0])) {
            $parameters[0] = json_encode($parameters[0]);
        }

        return call_user_func_array([$this->monolog, $method], $parameters);
    }
}
