<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumper;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\DumpException;

class PoDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     *
     * array[]
     *     ['locale'] string Language key
     *     ['Hello']  string Key is the source and value the target
     */
    public function dump(array $data): string
    {
        if (! isset($data['locale']) && $data['locale'] !== '') {
            throw new DumpException('No language key found; Please add [locale] to your data array.');
        }

        $output = 'msgid ""' . "\n";
        $output .= 'msgstr ""' . "\n";
        $output .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
        $output .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
        $output .= '"Language: ' . $data['locale'] . '\n"' . "\n";
        $output .= "\n";

        $newLine = false;
        $escape  = function ($str) {
            return addcslashes($str, "\0..\37\42\134");
        };

        unset($data['locale']);

        foreach ($data as $source => $target) {
            if ($newLine) {
                $output .= "\n";
            } else {
                $newLine = true;
            }

            $output .= sprintf('msgid "%s"' . "\n", $escape($source));
            $output .= sprintf('msgstr "%s"', $escape($target));
        }

        return $output;
    }
}
