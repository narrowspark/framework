<?php
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class INI implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        $ini = parse_ini_string($payload, true);

        if (!$ini) {
            throw new ParseException([
                'message' => 'Invalid INI provided.',
            ]);
        }

        return $ini;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        $output = '';

        foreach ($data as $section => $array) {
            $output .= $this->writeSection($section, $array);
        }

        return $output;
    }

    protected function writeSection($section, $array)
    {
        $subsections = [];
        $output = "[$section]\n";

        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $key = $section . '.' . $key;
                $subsections[$key] = (array) $value;
            } else {
                $output .= str_replace('=', '_', $key) . '=';

                if (is_string($value)) {
                    $output .= '"' . addslashes($value) . '"';
                } elseif (is_bool($value)) {
                    $output .= $value ? 'true' : 'false';
                } else {
                    $output .= $value;
                }

                $output .= "\n";
            }
        }

        if ($subsections) {
            $output .= "\n";

            foreach ($subsections as $section => $array) {
                $output .= $this->writeSection($section, $array);
            }
        }

        return $output;
    }
}
