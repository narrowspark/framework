<?php
namespace Viserio\Log\Traits;

use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\AmqpHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\CubeHandler;
use Monolog\Handler\RavenHandler;
use Monolog\Handler\ZendMonitorHandler;
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;


use Monolog\Logger as MonologLogger;

trait HandlerTrait
{
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
     * Parse the handler into a Monolog constant.
     *
     * @param string|object $handler
     * @param string        $path
     * @param string        $level
     * @param object|null   $processor
     * @param object|null   $formatter
     *
     * @return bool|null
     */
    public function parseHandler($handler, $path = '', $level = '', $processor = null, $formatter = null)
    {
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
     * Parse the string level into a Monolog constant.
     *
     * @param string $level
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function parseLevel($level)
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
     * FormatterTrait function.
     *
     * @param string|object $formatter
     */
    abstract public function parseFormatter($formatter);

    /**
     * Parse Processor.
     *
     * @param object            $handler
     * @param array|object|null $processors
     *
     * @return object
     */
    abstract protected function parseProcessor($handler, $processors = null);
}
