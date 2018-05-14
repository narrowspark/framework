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

namespace Viserio\Component\Support\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Traits\BytesFormatTrait;

/**
 * @internal
 *
 * @small
 */
final class BytesFormatTraitTest extends TestCase
{
    use BytesFormatTrait;

    /**
     * @param string $number
     * @param string $expected
     *
     * @dataProvider provideConvertToBytesCases
     */
    public function testConvertToBytes($number, $expected): void
    {
        self::assertEquals($expected, self::convertToBytes($number));
    }

    public function provideConvertToBytesCases(): iterable
    {
        return [
            'B' => ['1B', '1'],
            'KB' => ['3K', '3072'],
            'MB' => ['2M', '2097152'],
            'GB' => ['1G', '1073741824'],
            'regular spaces' => ['1 234 K', '1263616'],
            'no-break spaces' => ["1\xA0234\xA0K", '1263616'],
            'tab' => ["1\x09234\x09K", '1263616'],
            'coma' => ['1,234K', '1263616'],
            'dot' => ['1.234 K', '1263616'],
        ];
    }

    /**
     * @param string $number
     *
     * @dataProvider provideConvertToBytesBadFormatCases
     */
    public function testConvertToBytesBadFormat($number): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::convertToBytes($number);
    }

    public function provideConvertToBytesBadFormatCases(): iterable
    {
        return [
            'more than one unit of measure' => ['1234KB'],
            'unknown unit of measure' => ['1234Z'],
            'non-integer value' => ['1,234.56 K'],
        ];
    }

    /**
     * @param string $number
     * @param string $expected
     *
     * @dataProvider provideConvertToBytes64Cases
     */
    public function testConvertToBytes64($number, $expected): void
    {
        if (\PHP_INT_SIZE < 8) {
            self::markTestSkipped('A 64-bit system is required to perform this test.');
        }

        self::assertEquals($expected, self::convertToBytes($number));
    }

    public function provideConvertToBytes64Cases(): iterable
    {
        return [
            ['2T', '2199023255552'],
            ['1P', '1125899906842624'],
            ['2E', '2305843009213693952'],
        ];
    }

    public function testConvertToBytesInvalidArgument(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::convertToBytes('3Z');
    }

    public function testConvertToBytesOutOfBounds(): void
    {
        if (\PHP_INT_SIZE > 4) {
            self::markTestSkipped('A 32-bit system is required to perform this test.');
        }

        $this->expectException(\OutOfBoundsException::class);

        self::convertToBytes('2P');
    }
}
