<?php
declare(strict_types=1);
namespace Viserio\Log;

use DateTime;
use InvalidArgumentException;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Formatter\WildfireFormatter;
use Monolog\Handler\AmqpHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\CubeHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\RavenHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\ZendMonitorHandler;
use Monolog\Logger as MonologLogger;
use RuntimeException;
use Viserio\Log\Traits\ParseLevelTrait;

class HandlerParser
{
    use ParseLevelTrait;

    /**
     * The Monolog logger instance.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;

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
        'errorLog'  => ErrorLogHandler::class,
        'loggly'    => LogglyHandler::class,
        'syslogUdp' => SyslogUdpHandler::class,
        //Browser
        'browser'   => BrowserConsoleHandler::class,
        'firePHP'   => FirePHPHandler::class,
        'chromePHP' => ChromePHPHandler::class,
    ];

    /**
     * All of the formatter.
     *
     * @var array
     */
    protected $formatter = [
        'line'       => LineFormatter::class,
        'html'       => HtmlFormatter::class,
        'normalizer' => NormalizerFormatter::class,
        'scalar'     => ScalarFormatter::class,
        'json'       => JsonFormatter::class,
        'wildfire'   => WildfireFormatter::class,
        'chrome'     => ChromePHPFormatter::class,
        'gelf'       => GelfMessageFormatter::class,
    ];

    /**
     * Create a new Log handler parser instance.
     *
     * @param \Monolog\Logger $monolog
     */
    public function __construct(MonologLogger $monolog)
    {
        $this->monolog = $monolog;
    }

    /**
     * Parse the handler into a Monolog constant.
     *
     * @param string|object        $handler
     * @param string               $path
     * @param string               $level
     * @param object|string[]|null $processor
     * @param object|string|null   $formatter
     */
    public function parseHandler(
        $handler,
        string $path = '',
        string $level = '',
        $processor = null,
        $formatter = null
    ) {
        $customHandler = $this->validateHandler($handler, $path, $level);

        $customHandler = $this->parseProcessor($customHandler, $processor);

        if ($formatter !== null) {
            $customHandler->setFormatter($this->parseFormatter($formatter));
        }

        $this->monolog->pushHandler($customHandler);
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
     * Parse Processor.
     *
     * @param \Monolog\Handler\HandlerInterface $handler
     * @param array|object|null                 $processors
     *
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function parseProcessor(HandlerInterface $handler, $processors = null): HandlerInterface
    {
        if ($processors === null) {
            return $handler;
        }

        if (is_array($processors)) {
            foreach ($processors as $processor => $settings) {
                $handler->pushProcessor(new $processor($settings));
            }
        } elseif (is_object($processors)) {
            $handler->pushProcessor($processors);
        }

        return $handler;
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
                return new $this->formatter['line']($this->lineFormatterSettings(), 'H:i:s', true, true);
            case 'html':
                return new $this->formatter['html'](DateTime::RFC2822);
            case 'normalizer':
                return new $this->formatter['normalizer']();
            case 'scalar':
                return new $this->formatter['scalar']();
            case 'json':
                return new $this->formatter['json']();
            case 'wildfire':
                return new $this->formatter['wildfire']();
            case 'chrome':
                return new $this->formatter['chrome']();
            case 'gelf':
                return new $this->formatter['gelf']();
            default:
                throw new InvalidArgumentException('Invalid formatter.');
        }
    }

    /**
     * Layout for LineFormatter.
     *
     * @return string
     */
    protected function lineFormatterSettings(): string
    {
        $options = [
            'gray'   => "\033[37m",
            'green'  => "\033[32m",
            'yellow' => "\033[93m",
            'blue'   => "\033[94m",
            'purple' => "\033[95m",
            'white'  => "\033[97m",
            'bold'   => "\033[1m",
            'reset'  => "\033[0m",
        ];

        $width     = getenv('COLUMNS') ?: 60; // Console width from env, or 60 chars.
        $separator = str_repeat('━', (int) $width); // A nice separator line

        $format = $options['bold'];
        $format .= $options['green'] . '[%datetime%]';
        $format .= $options['white'] . '[%channel%.';
        $format .= $options['yellow'] . '%level_name%';
        $format .= sprintf('%s]', $options['white']);
        $format .= $options['blue'] . '[UID:%extra.uid%]';
        $format .= $options['purple'] . '[PID:%extra.process_id%]';
        $format .= sprintf('%s:%s', $options['reset'], PHP_EOL);
        $format .= '%message%' . PHP_EOL;
        $format .= sprintf('%s%s%s%s', $options['gray'], $separator, $options['reset'], PHP_EOL);

        return $format;
    }

    /**
     * Validate handler var.
     *
     * @param string|object $handler
     * @param string        $path
     * @param string        $level
     *
     * @throws \RuntimeException
     *
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function validateHandler($handler, string $path, string $level): HandlerInterface
    {
        if (is_object($handler) && $handler instanceof HandlerInterface) {
            return $handler;
        } elseif (is_string($handler) && isset($this->handler[$handler])) {
            return new $this->handler[$handler]($path, $this->parseLevel($level));
        }

        throw new RuntimeException(
            sprintf(
                'Handler [%s] dont exist.',
                is_object($handler) ?
                get_class($handler) :
                $handler
            )
        );
    }
}
