<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

trait BytesFormatTrait
{
    /**
     * Convert a number string to bytes.
     *
     * @param string $memoryLimit
     *
     * @return int limit in bytes or -1 if it's unlimited
     */
    protected function convertToBytes(string $memoryLimit): int
    {
        if ($memoryLimit === '-1') {
            return -1;
        }

        $memoryLimit = \mb_strtolower($memoryLimit);
        $max         = \mb_strtolower(\ltrim($memoryLimit, '+'));

        if (\mb_strpos($max, '0x') === 0) {
            $max = \intval($max, 16);
        } elseif (\mb_strpos($max, '0') === 0) {
            $max = \intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (\mb_substr($memoryLimit, -1)) {
            case 't':
                $max *= 1024;

                break;
            case 'g':
                $max *= 1024;

                break;
            case 'm':
                $max *= 1024;

                break;
            case 'k':
                $max *= 1024;

                break;
        }

        return $max;
    }
}
