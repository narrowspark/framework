<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser\Dumper;

use Viserio\Contract\Parser\Dumper as DumperContract;

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
        /** @var array<array, string> $subsections */
        $subsections = [];
        $eol = "\n";
        $output = "[{$section}]{$eol}";

        foreach ($array as $key => $value) {
            if (\is_array($value) || \is_object($value)) {
                $subsections[$key] = \is_array($value) ? $value : (array) $value;
            } else {
                $output .= \str_replace('=', '_', $key) . '=';
                $output .= self::export($value);
                $output .= $eol;
            }
        }

        if (\count($subsections) !== 0) {
            $output .= $eol;

            foreach ($subsections as $subsection => $data) {
                foreach ($data as $key => $value) {
                    $output .= $subsection . '[' . (\is_string($key) ? $key : '') . ']=' . self::export($value);
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
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_numeric($value)) {
            return '"' . $value . '"';
        }

        return \sprintf('"%s"', $value);
    }
}
