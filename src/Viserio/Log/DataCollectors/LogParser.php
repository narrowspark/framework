<?php
declare(strict_types=1);
namespace Viserio\Log\DataCollectors;

use Viserio\Log\Traits\ParseLevelTrait;

class LogParser
{
    use ParseLevelTrait;

    /**
     * The max size of a log file.
     */
    public const MAX_FILE_SIZE = 52428800;

    public const REGEX_DATE_PATTERN = '\b\d{4}\-\d{1,2}\-\d{1,2}\b';

    public const REGEX_TIME_PATTERN = '\d{1,2}\:\d{1,2}\:\d{1,2}\b';

    /**
     * Parsed data.
     *
     * @var array
     */
    protected $parsed = [];

    /**
     * Parse log file content.
     *
     * @param string $path
     *
     * @return array
     */
    public function parse(string $path): array
    {
        if (filesize($path) <= self::MAX_FILE_SIZE) {
            $raw = file_get_contents($path);
        } else {
            // Load the latest lines, guessing about 15x the number of log entries (for stack traces etc)
            $raw = $this->tailFile($path, 124);
        }

        $log = $this->parseRawData($raw);

        // @codeCoverageIgnoreStart
        if (! is_array($log)) {
            return [];
        }
        // @codeCoverageIgnoreEnd

        $parsed = [];

        foreach ($log as $heading) {
            for ($i = 0, $j = count($heading); $i < $j; ++$i) {
                $parsed[] = $this->populateEntries($heading, $i);
            }
        }

        unset($log);

        return array_reverse($parsed);
    }

    /**
     * Parse raw log data.
     *
     * @param string $raw
     *
     * @return array
     */
    protected function parseRawData(string $raw): array
    {
        $pattern = '/\[' . self::REGEX_DATE_PATTERN . '\ ' . self::REGEX_TIME_PATTERN . '\].*/';

        preg_match_all($pattern, $raw, $log);

        return $log;
    }

    /**
     * Populate entries.
     *
     * @param array $heading
     * @param int   $key
     *
     * @return array
     */
    protected function populateEntries(array $heading, int $key): array
    {
        foreach ($this->levels as $level => $monologLevel) {
            if (mb_strpos(mb_strtolower($heading[$key]), mb_strtolower('.' . $level)) !== false) {
                return [
                    $level,
                    $heading[$key],
                ];
            }
        }
    }

    /**
     * By Ain Tohvri (ain).
     *
     * @see http://tekkie.flashbit.net/php/tail-functionality-in-php
     *
     * @param string $file
     * @param int    $lines
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function tailFile(string $file, int $lines): array
    {
        $handle      = fopen($file, 'r');
        $linecounter = $lines;
        $pos         = -2;
        $beginning   = false;
        $text        = [];

        while ($linecounter > 0) {
            $t = ' ';

            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }

                $t = fgetc($handle);
                --$pos;
            }

            --$linecounter;

            if ($beginning) {
                rewind($handle);
            }

            $text[$lines - $linecounter - 1] = fgets($handle);

            if ($beginning) {
                break;
            }
        }

        fclose($handle);

        return array_reverse($text);
    }
}
