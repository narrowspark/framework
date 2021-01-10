<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Support\Traits;

use InvalidArgumentException;
use OutOfBoundsException;

trait BytesFormatTrait
{
    /**
     * Convert a number string to bytes.
     *
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     *
     * @return int limit in bytes or -1 if it's unlimited
     */
    protected static function convertToBytes(string $number): int
    {
        /**
         * Prefixes to specify unit of measure for memory amount.
         *
         * Warning: it is important to maintain the exact order of letters in this literal,
         * as it is used to convert string with units to bytes
         */
        $memoryUnits = 'BKMGTPE';

        if (\preg_match('/^(.*\d)\h*(\D)$/', $number, $matches) !== 1) {
            throw new InvalidArgumentException("Number format '{$number}' is not recognized.");
        }

        $unitSymbol = \strtoupper($matches[2]);

        if (\strpos($memoryUnits, $unitSymbol) === false) {
            throw new InvalidArgumentException("The number '{$number}' has an unrecognized unit: '{$unitSymbol}'.");
        }

        $result = self::convertToNumber($matches[1]);
        $pow = $unitSymbol ? \strpos($memoryUnits, $unitSymbol) : 0;

        if (\PHP_INT_SIZE <= 4 && $pow >= 4) {
            throw new OutOfBoundsException('A 32-bit system is unable to process such a number.');
        }

        if ($unitSymbol) {
            $result *= 1024 ** $pow;
        }

        return (int) $result;
    }

    /**
     * Remove non-numeric characters in the string to cast it to a numeric value.
     *
     * Incoming number can be presented in arbitrary format that depends on locale. We don't possess locale information.
     * So the best can be done is to treat number as an integer and eliminate delimiters.
     * Method will not behave correctly with non-integer numbers for the following reason:
     * - if value has more than one delimiter, such as in French notation: "1 234,56" -- then we can infer decimal part
     * - but the value has only one delimiter, such as "234,56", then it is impossible to know whether it is decimal
     *   separator or not. Only knowing the right format would allow this.
     *
     * @throws InvalidArgumentException
     */
    private static function convertToNumber(string $number): string
    {
        \preg_match_all('/(\D+)/', $number, $matches);

        if (\count(\array_unique($matches[0])) > 1) {
            throw new InvalidArgumentException("The number '{$number}' seems to have decimal part. Only integer numbers are supported.");
        }

        return \preg_replace('/\D+/', '', $number);
    }
}
