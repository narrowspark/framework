<?php
declare(strict_types=1);
namespace Viserio\Bridge\Monolog\Formatter;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use DateTimeInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * Formats incoming records for console output by coloring them depending on log level.
 *
 * @author Tobias Schultze <http://tobion.de>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class ConsoleFormatter implements FormatterInterface
{
    public const SIMPLE_FORMAT = "%datetime% %start_tag%%level_name%%end_tag% <comment>[%channel%]</> %message%%context%%extra%\n";
    public const SIMPLE_DATE   = 'H:i:s';

    /**
     * Mapper for monolog level to color level.
     *
     * @var array
     */
    private static $levelColorMap = [
        Logger::DEBUG     => 'fg=white',
        Logger::INFO      => 'fg=green',
        Logger::NOTICE    => 'fg=blue',
        Logger::WARNING   => 'fg=cyan',
        Logger::ERROR     => 'fg=yellow',
        Logger::CRITICAL  => 'fg=red',
        Logger::ALERT     => 'fg=red',
        Logger::EMERGENCY => 'fg=white;bg=red',
    ];

    /**
     * Console formatter configuration.
     *
     * @var array
     */
    private $options;

    /**
     * Stream data.
     *
     * @var mixed
     */
    private $outputBuffer;

    /**
     * Configured VarCloner instance.
     *
     * @var \Symfony\Component\VarDumper\Cloner\VarCloner
     */
    private $cloner;

    /**
     * CliDumper instance.
     *
     * @var \Symfony\Component\VarDumper\Dumper\CliDumper
     */
    private $dumper;

    /**
     * Create a new console formatter instance.
     *
     * Available options:
     *   * format: The format of the outputted log string. The following placeholders are supported: %datetime%, %start_tag%, %level_name%, %end_tag%, %channel%, %message%, %context%, %extra%;
     *   * date_format: The format of the outputted date string;
     *   * colors: If true, the log string contains ANSI code to add color;
     *   * multiline: If false, "context" and "extra" are dumped on one line.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = \array_replace([
            'format'      => self::SIMPLE_FORMAT,
            'date_format' => self::SIMPLE_DATE,
            'colors'      => true,
            'multiline'   => false,
        ], $options);

        $casterClass = $this->castObjectClass();

        if (\class_exists(VarCloner::class)) {
            $this->cloner = new VarCloner();
            $this->cloner->addCasters([
                '*' => [$casterClass, 'castObject'],
            ]);

            $this->outputBuffer = \fopen('php://memory', 'r+b');

            $output = [$this, 'echoLine'];

            if ($this->options['multiline']) {
                $output = $this->outputBuffer;
            }

            // Exits from VarDumper version >=3.3
            $commaSeparator = \defined(CliDumper::class . '::DUMP_COMMA_SEPARATOR') ? CliDumper::DUMP_COMMA_SEPARATOR : 4;

            $this->dumper = new CliDumper($output, null, CliDumper::DUMP_LIGHT_ARRAY | $commaSeparator);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record     = $this->replacePlaceHolder($record);
        $levelColor = self::$levelColorMap[$record['level']];

        if ($this->options['multiline']) {
            $context = $extra = "\n";
        } else {
            $context = $extra = ' ';
        }

        $context .= $this->dumpData($record['context']);
        $extra   .= $this->dumpData($record['extra']);

        return \strtr($this->options['format'], [
            '%datetime%'   => $record['datetime']->format($this->options['date_format']),
            '%start_tag%'  => \sprintf('<%s>', $levelColor),
            '%level_name%' => \sprintf('%-9s', $record['level_name']),
            '%end_tag%'    => '</>',
            '%channel%'    => $record['channel'],
            '%message%'    => $this->replacePlaceHolder($record)['message'],
            '%context%'    => $context,
            '%extra%'      => $extra,
        ]);
    }

    /**
     * @internal
     *
     * @param mixed $line
     * @param mixed $depth
     * @param mixed $indentPad
     *
     * @return void
     */
    public function echoLine($line, $depth, $indentPad): void
    {
        if (-1 !== $depth) {
            \fwrite($this->outputBuffer, $line);
        }
    }

    /**
     * Return a anonymous class with a castObject function.
     *
     * @return object
     *
     * @codeCoverageIgnore
     */
    private function castObjectClass(): object
    {
        return new class($this->options) {
            /**
             * Console formatter configuration.
             *
             * @var array
             */
            private $options;

            public function __construct(array $options)
            {
                $this->options = $options;
            }

            /**
             * @param mixed                                    $value
             * @param array                                    $array
             * @param \Symfony\Component\VarDumper\Cloner\Stub $stub
             * @param mixed                                    $isNested
             *
             * @return array
             */
            public function castObject($value, array $array, Stub $stub, $isNested): array
            {
                if ($this->options['multiline']) {
                    return $array;
                }

                if ($isNested && ! $value instanceof DateTimeInterface) {
                    $stub->cut = -1;
                    $array     = [];
                }

                return $array;
            }
        };
    }

    /**
     * Replace message and context place holder.
     *
     * @param array $record
     *
     * @return array
     */
    private function replacePlaceHolder(array $record): array
    {
        $message = $record['message'];

        if (\mb_strpos($message, '{') === false) {
            return $record;
        }

        $context = $record['context'];

        $replacements = [];

        foreach ((array) $context as $k => $v) {
            // Remove quotes added by the dumper around string.
            $v                            = \trim($this->dumpData($v, false), '"');
            $v                            = OutputFormatter::escape($v);
            $replacements['{' . $k . '}'] = \sprintf('<comment>%s</>', $v);
        }

        $record['message'] = \strtr($message, $replacements);

        return $record;
    }

    /**
     * Dump console data.
     *
     * @param mixed     $data
     * @param null|bool $colors
     *
     * @return string
     */
    private function dumpData($data, ?bool $colors = null): string
    {
        if ($this->dumper === null) {
            return '';
        }

        if ($colors === null) {
            $this->dumper->setColors($this->options['colors']);
        } else {
            $this->dumper->setColors($colors);
        }

        if (! $data instanceof Data) {
            $data = $this->cloner->cloneVar($data);
        }

        $data = $data->withRefHandles(false);
        $this->dumper->dump($data);

        $dump = \stream_get_contents($this->outputBuffer, -1, 0);

        \rewind($this->outputBuffer);
        \ftruncate($this->outputBuffer, 0);

        return \rtrim($dump);
    }
}
