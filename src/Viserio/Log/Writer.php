<?php
namespace Viserio\Log;

use DateTime;
use Monolog\Formatter\{
    ChromePHPFormatter,
    ElasticaFormatter,
    FormatterInterface,
    GelfFormatter,
    HtmlFormatter,
    JsonFormatter,
    LineFormatter,
    LogstashFormatter,
    NormalizerFormatter,
    ScalarFormatter,
    WildfireFormatter
};
use Monolog\Handler\{
    ErrorLogHandler,
    RotatingFileHandler
};
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Contracts\{
    Events\Dispatcher as DispatcherContract,
    Log\Log as LogContract
};

class Writer implements LogContract, PsrLoggerInterface
{
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
     * All of the handler.
     *
     * @var array
     */
    protected $handler = [
        'stream'      => StreamHandler::class,
        'amqp'        => AmqpHandler::class,
        'gelf'        => GelfHandler::class,
        'cube'        => CubeHandler::class,
        'raven'       => RavenHandler::class,
        'zendMonitor' => ZendMonitorHandler::class,
        'newRelic'    => NewRelicHandler::class,
        //Log
        'errorLog'    => ErrorLogHandler::class,
        'loggly'      => LogglyHandler::class,
        'syslogUdp'   => SyslogUdpHandler::class,
        //Browser
        'browser'     => BrowserConsoleHandler::class,
        'firePHP'     => FirePHPHandler::class,
        'chromePHP'   => ChromePHPHandler::class,
    ];

    /**
     * All of the error levels.
     *
     * @var array
     */
    protected $levels = [
        'debug'     => MonologLogger::DEBUG,
        'info'      => MonologLogger::INFO,
        'notice'    => MonologLogger::NOTICE,
        'warning'   => MonologLogger::WARNING,
        'error'     => MonologLogger::ERROR,
        'critical'  => MonologLogger::CRITICAL,
        'alert'     => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];

    /**
     * Create a new log writer instance.
     *
     * @param \Monolog\Logger    $monolog
     * @param DispatcherContract $dispatcher
     */
    public function __construct(MonologLogger $monolog, DispatcherContract $dispatcher = null)
    {
        // PSR 3 log message formatting for all handlers
        $monolog->pushProcessor(new PsrLogMessageProcessor());

        $this->monolog = $monolog;
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
        $this->parseHandler('stream', $path, $level, $processor, $formatter);
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
        $this->parseHandler(
            new RotatingFileHandler($path, $days, $this->parseLevel($level)),
            '',
            '',
            $processor,
            $formatter
        );
    }

    /**
     * {@inheritdoc}
     */
    public function useErrorLog(
        string $level = 'debug',
        int $messageType = ErrorLogHandler::OPERATING_SYSTEM,
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
     * {@inheritdoc}
     */
    public function parseHandler(
        $handler,
        string $path = '',
        string $level = '',
        $processor = null,
        $formatter = null
    ) {
        if (is_object($handler)) {
            $customHandler = $handler;
        } else {
            $customHandler = new $this->handler[$handler]($path, $this->parseLevel($level));
        }

        $customHandler = $this->parseProcessor($customHandler, $processor);

        if ($formatter !== null) {
            $customHandler->setFormatter($this->parseFormatter($formatter));
        }

        $this->monolog->pushHandler($customHandler);
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
     * @param DispatcherContract $dispatcher
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
     * @return DispatcherContract
     */
    public function getEventDispatcher(): DispatcherContract
    {
        return $this->dispatcher;
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
        // If the event dispatcher is set, we will pass along the parameters to the
        // log listeners. These are useful for building profilers or other tools
        // that aggregate all of the log messages for a given "request" cycle.
        if ($this->dispatcher !== null &&
            in_array($method, ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])
        ) {
            $this->dispatcher->emit('viserio.log', $parameters);
        }

        return call_user_func_array([$this->monolog, $method], $parameters);
    }

    /**
     * Parse Processor.
     *
     * @param object            $handler
     * @param array|object|null $processors
     *
     * @return object
     */
    protected function parseProcessor($handler, $processors = null)
    {
        if (is_array($processors)) {
            foreach ($processors as $processor => $settings) {
                $handler->pushProcessor(new $processor($settings));
            }
        } elseif ($processors !== null) {
            $handler->pushProcessor($processors);
        }

        return $handler;
    }

    /**
     * Layout for LineFormatter.
     *
     * @return string
     */
    protected function lineFormatterSettings(): string
    {
        $color = [
            'gray' => "\033[37m",
            'green' => "\033[32m",
            'yellow' => "\033[93m",
            'blue' => "\033[94m",
            'purple' => "\033[95m",
            'white' => "\033[97m",
            'bold' => "\033[1m",
            'reset' => "\033[0m",
        ];

        $width = getenv('COLUMNS') ?: 60; // Console width from env, or 60 chars.
        $separator = str_repeat('━', $width); // A nice separator line

        $format = sprintf('%s', $color['bold']);
        $format .= sprintf('%s[%datetime%]', $color['green']);
        $format .= sprintf('%s[%channel%.', $color['white']);
        $format .= sprintf('%s%level_name%', $color['yellow']);
        $format .= sprintf('%s]', $color['white']);
        $format .= sprintf('%s[UID:%extra.uid%]', $color['blue']);
        $format .= sprintf('%s[PID:%extra.process_id%]', $color['purple']);
        $format .= sprintf('%s:%s', $color['reset'], PHP_EOL);
        $format .= '%message%' . PHP_EOL;
        $format .= sprintf('%s%s%s%s', $color['gray'], $separator, $color['reset'], PHP_EOL);

        return $format;
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param string $level
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function parseLevel($level): int
    {
        if (is_object($level)) {
            return $level;
        }

        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }

    /**
     * Parse the formatter into a Monolog constant.
     *
     * @param string|object $formatter
     *
     * @throws \InvalidArgumentException
     *
     * @return \Monolog\Formatter\FormatterInterface
     */
    protected function parseFormatter($formatter): FormatterInterface
    {
        if (is_object($formatter)) {
            return $formatter;
        }

        switch ($formatter) {
            case 'line':
                $format = $this->formatter['line']($this->lineFormatterSettings(), 'H:i:s', true);
                break;
            case 'html':
                $format = $this->formatter['html'](DateTime::RFC2822);
                break;
            case 'normalizer':
                $format = $this->formatter['normalizer']();
                break;
            case 'scalar':
                $format = $this->formatter['scalar']();
                break;
            case 'json':
                $format = $this->formatter['json']();
                break;
            case 'wildfire':
                $format = $this->formatter['wildfire']();
                break;
            case 'chrome':
                $format = $this->formatter['chrome']();
                break;
            case 'gelf':
                $format = $this->formatter['gelf']();
                break;
            case 'logstash':
                $format = $this->formatter['logstash']();
                break;
            case 'elastica':
                $format = $this->formatter['elastica']();
                break;

            default:
                throw new InvalidArgumentException('Invalid formatter.');
        }

        return new $format();
    }
}
