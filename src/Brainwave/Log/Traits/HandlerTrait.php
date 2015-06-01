<?php

namespace Brainwave\Log\Traits;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Monolog\Logger as MonologLogger;

/**
 * HandlerTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
trait HandlerTrait
{
    /**
     * All of the handler.
     *
     * @var array
     */
    protected $handler = [
        'stream' => '\Monolog\Handler\StreamHandler',
        'amqp' => '\Monolog\Handler\AmqpHandler',
        'gelf' => '\Monolog\Handler\GelfHandler',
        'cube' => '\Monolog\Handler\CubeHandler',
        'raven' => '\Monolog\Handler\RavenHandler',
        'zendMonitor' => '\Monolog\Handler\ZendMonitorHandler',
        'newRelic' => '\Monolog\Handler\NewRelicHandler',
        //Log
        'errorLog' => '\Monolog\Handler\ErrorLogHandler',
        'loggly' => '\Monolog\Handler\LogglyHandler',
        'syslogUdp' => '\Monolog\Handler\SyslogUdpHandler',
        //Browser
        'browser' => '\Monolog\Handler\BrowserConsoleHandler',
        'firePHP' => '\Monolog\Handler\FirePHPHandler',
        'chromePHP' => '\Monolog\Handler\ChromePHPHandler',
    ];

    /**
     * All of the error levels.
     *
     * @var array
     */
    protected $levels = [
        'debug' => MonologLogger::DEBUG,
        'info' => MonologLogger::INFO,
        'notice' => MonologLogger::NOTICE,
        'warning' => MonologLogger::WARNING,
        'error' => MonologLogger::ERROR,
        'critical' => MonologLogger::CRITICAL,
        'alert' => MonologLogger::ALERT,
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

        throw new \InvalidArgumentException('Invalid log level.');
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
