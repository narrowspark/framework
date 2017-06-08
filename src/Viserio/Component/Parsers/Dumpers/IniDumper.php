<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;

class IniDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $output = '';

        foreach ($data as $section => $array) {
            $output .= $this->writeSection($section, $array);
        }

        return $output;
    }

    /**
     * @param string $section
     * @param array  $array
     *
     * @return string
     */
    protected function writeSection(string $section, array $array): string
    {
        $subsections = [];
        $output      = '[' . $section . ']' . PHP_EOL;

        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $subsections[$key] = is_array($value) ? $value : (array) $value;
            } else {
                $output .= str_replace('=', '_', $key) . '=';
                $output .= self::export($value);
                $output .= PHP_EOL;
            }
        }

        if (! empty($subsections)) {
            $output .= PHP_EOL;

            foreach ($subsections as $section => $data) {
                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        $output .= $section . '[' . (is_string($key) ? $key : '') . ']=' . self::export($value);
                    }
                } else {
                    $output .= $section . '[]=' . $data;
                }
            }
        }

        return $output;
    }

    /**
     * Converts the supplied value into a valid ini representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    private static function export($value): string
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_numeric($value)) {
            return '"' . var_export($value, true) . '"';
        }

        return sprintf('"%s"', $value);
    }

    /**
     * Normalizes INI and other values.
     *
     * @param mixed $value
     *
     * @return bool|int|null|string|array
     */
    private static function normalize($value)
    {
        // Normalize array values
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = self::normalize($subValue);
            }

            return $value;
        }

        // Don't normalize non-string value
        if (! is_string($value)) {
            return $value;
        }

        // Normalize true boolean value
        if (self::compareValues($value, ['true', 'on', 'yes'])) {
            return true;
        }

        // Normalize false boolean value
        if (self::compareValues($value, ['false', 'off', 'no', 'none'])) {
            return false;
        }

        // Normalize null value
        if (self::compareValues($value, ['null'])) {
            return null;
        }

        // Normalize numeric value
        if (is_numeric($value)) {
            $numericValue = $value + 0;

            if ((is_int($numericValue) && (int) $value === $numericValue)
                || (is_float($numericValue) && (float) $value === $numericValue)
            ) {
                $value = $numericValue;
            }
        }

        return $value;
    }

    /**
     * Case insensitively compares values.
     *
     * @param string $value
     * @param array  $comparisons
     *
     * @return bool
     */
    private static function compareValues(string $value, array $comparisons): bool
    {
        foreach ($comparisons as $comparison) {
            if (0 === strcasecmp($value, $comparison)) {
                return true;
            }
        }

        return false;
    }
}
