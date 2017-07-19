<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumper;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;

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
            if (\is_array($value) || \is_object($value)) {
                $subsections[$key] = \is_array($value) ? $value : (array) $value;
            } else {
                $output .= \str_replace('=', '_', $key) . '=';
                $output .= self::export($value);
                $output .= PHP_EOL;
            }
        }

        if (! empty($subsections)) {
            $output .= PHP_EOL;

            foreach ($subsections as $section => $data) {
                if (\is_array($data)) {
                    foreach ($data as $key => $value) {
                        $output .= $section . '[' . (\is_string($key) ? $key : '') . ']=' . self::export($value);
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
        if (null === $value) {
            return 'null';
        } elseif (\is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (\is_numeric($value)) {
            return '"' . $value . '"';
        }

        return \sprintf('"%s"', $value);
    }
}
