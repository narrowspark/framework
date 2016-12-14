<?php
declare(strict_types=1);
namespace Viserio\Log\DataCollectors;

use Viserio\Log\Traits\ParseLevelTrait;

class LogParser
{
    use ParseLevelTrait;

    /**
     * Parsed data.
     *
     * @var array
     */
    protected $parsed = [];

    /**
     * The max size of a log file.
     */
    const MAX_FILE_SIZE = 52428800;

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

        list($headings, $data) = $this->parseRawData($raw);

        // @codeCoverageIgnoreStart
        if (! is_array($headings)) {
            return $this->parsed;
        }
        // @codeCoverageIgnoreEnd

        foreach ($headings as $heading) {
            for ($i = 0, $j = count($heading); $i < $j; $i++) {
                $this->populateEntries($heading, $data, $i);
            }
        }

        unset($headings, $data);

        return array_reverse($this->parsed);
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
        $pattern = '/\[' . REGEX_DATE_PATTERN . ' ' . REGEX_TIME_PATTERN . '\].*/';

        preg_match_all($pattern, $raw, $headings);

        $data = preg_split($pattern, $raw);

        if ($data[0] < 1) {
            $trash = array_shift($data);
            unset($trash);
        }

        return [$headings, $data];
    }

    /**
     * Populate entries.
     *
     * @param array $heading
     * @param array $data
     * @param int   $key
     */
    protected function populateEntries(array $heading, array $data, int $key): void
    {
        foreach ($this->levels as $level => $monologLevel) {
            if (strpos(strtolower([$key]), strtolower('.' . $level)) !== false) {
                $this->parsed[] = [
                    $level,
                    $heading[$key],
                    $data[$key],
                ];
            }
        }
    }

    /**
     * By Ain Tohvri (ain)
     *
     * @see http://tekkie.flashbit.net/php/tail-functionality-in-php
     *
     * @param string $file
     * @param int    $lines
     *
     * @return array
     */
    protected function tailFile(string $file, int $lines): array
    {
        $handle = fopen($file, 'r');
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];

        while ($linecounter > 0) {
            $t = ' ';

            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }

                $t = fgetc($handle);
                $pos--;
            }

            $linecounter--;

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
