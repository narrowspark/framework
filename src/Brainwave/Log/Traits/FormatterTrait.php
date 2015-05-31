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

/**
 * FormatterTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
trait FormatterTrait
{
    /**
     * All of the formatter.
     *
     * @var array
     */
    protected $formatter = [
        'line' => '\Monolog\Formatter\LineFormatter',
        'html' => '\Monolog\Formatter\HtmlFormatter',
        'normalizer' => '\Monolog\Formatter\NormalizerFormatter',
        'scalar' => '\Monolog\Formatter\ScalarFormatter',
        'json' => '\Monolog\Formatter\JsonFormatter',
        'wildfire' => '\Monolog\Formatter\WildfireFormatter',
        'chrome' => '\Monolog\Formatter\ChromePHPFormatter',
        'gelf' => '\Monolog\Formatter\GelfFormatter',
        'logstash' => '\Monolog\Formatter\LogstashFormatter',
        'elastica' => '\Monolog\Formatter\ElasticaFormatter',
    ];

    /**
     * Layout for LineFormatter.
     *
     * @return string
     */
    public function lineFormatterSettings()
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

        $width = getenv('COLUMNS') ?: 60; # Console width from env, or 60 chars.
        $separator = str_repeat('━', $width); # A nice separator line

        $format = sprintf('%s', $color['bold']);
        $format .= sprintf('%s[%datetime%]', $color['green']);
        $format .= sprintf('%s[%channel%.', $color['white']);
        $format .= sprintf('%s%level_name%', $color['yellow']);
        $format .= sprintf('%s]', $color['white']);
        $format .= sprintf('%s[UID:%extra.uid%]', $color['blue']);
        $format .= sprintf('%s[PID:%extra.process_id%]', $color['purple']);
        $format .= sprintf('%s:%s', $color['reset'], PHP_EOL);
        $format .= '%message%'.PHP_EOL;
        $format .= sprintf('%s%s%s%s', $color['gray'], $separator, $color['reset'], PHP_EOL);

        return $format;
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
    protected function parseFormatter($formatter)
    {
        if (is_object($formatter)) {
            return $formatter;
        }

        switch ($formatter) {
            case 'line':
                $format = $this->formatter['line']($this->lineFormatterSettings(), 'H:i:s', true);
                break;
            case 'html':
                $format = $this->formatter['html'](\DateTime::RFC2822);
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
                throw new \InvalidArgumentException('Invalid formatter.');
        }

        return new $format();
    }
}
