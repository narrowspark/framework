<?php
namespace Viserio\Log;

use InvalidArgumentException;
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
    AmqpHandler,
    BrowserConsoleHandler,
    ChromePHPHandler,
    CubeHandler,
    ErrorLogHandler,
    FirePHPHandler,
    GelfHandler,
    HandlerInterface,
    LogglyHandler,
    NewRelicHandler,
    RavenHandler,
    StreamHandler,
    SyslogUdpHandler,
    ZendMonitorHandler
};
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
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
        'errorLog'    => ErrorLogHandler::class,
        'loggly'      => LogglyHandler::class,
        'syslogUdp'   => SyslogUdpHandler::class,
        //Browser
        'browser'     => BrowserConsoleHandler::class,
        'firePHP'     => FirePHPHandler::class,
        'chromePHP'   => ChromePHPHandler::class,
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
        'gelf'       => GelfFormatter::class,
        'logstash'   => LogstashFormatter::class,
        'elastica'   => ElasticaFormatter::class,
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
     * @param string|object $handler
     * @param string        $path
     * @param string        $level
     * @param object|null   $processor
     * @param object|null   $formatter
     *
     * @return void
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
     * Parse Processor.
     *
     * @param HandlerInterface  $handler
     * @param array|object|null $processors
     *
     * @return HandlerInterface
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
        } elseif ($processors !== null) {
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
}
