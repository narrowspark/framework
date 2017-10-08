<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Traits\BytesFormatTrait;

class BytesFormatTraitTest extends TestCase
{
    use BytesFormatTrait;

    /**
     * @param string $number
     * @param string $expected
     *
     * @dataProvider convertToBytesDataProvider
     */
    public function testConvertToBytes($number, $expected): void
    {
        self::assertEquals($expected, self::convertToBytes($number));
    }

    /**
     * @return array
     */
    public function convertToBytesDataProvider()
    {
        return [
            'B'               => ['1B', '1'],
            'KB'              => ['3K', '3072'],
            'MB'              => ['2M', '2097152'],
            'GB'              => ['1G', '1073741824'],
            'regular spaces'  => ['1 234 K', '1263616'],
            'no-break spaces' => ["1\xA0234\xA0K", '1263616'],
            'tab'             => ["1\x09234\x09K", '1263616'],
            'coma'            => ['1,234K', '1263616'],
            'dot'             => ['1.234 K', '1263616'],
        ];
    }

    /**
     * @param string $number
     *
     * @dataProvider convertToBytesBadFormatDataProvider
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertToBytesBadFormat($number)
    {
        self::convertToBytes($number);
    }

    /**
     * @return array
     */
    public function convertToBytesBadFormatDataProvider()
    {
        return [
            'more than one unit of measure' => ['1234KB'],
            'unknown unit of measure'       => ['1234Z'],
            'non-integer value'             => ['1,234.56 K'],
        ];
    }

    /**
     * @param string $number
     * @param string $expected
     *
     * @dataProvider convertToBytes64DataProvider
     */
    public function testConvertToBytes64($number, $expected)
    {
        if (PHP_INT_SIZE <= 4) {
            $this->markTestSkipped('A 64-bit system is required to perform this test.');
        }

        self::assertEquals($expected, self::convertToBytes($number));
    }

    /**
     * @return array
     */
    public function convertToBytes64DataProvider()
    {
        return [
            ['2T', '2199023255552'],
            ['1P', '1125899906842624'],
            ['2E', '2305843009213693952'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConvertToBytesInvalidArgument()
    {
        self::convertToBytes('3Z');
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testConvertToBytesOutOfBounds()
    {
        if (PHP_INT_SIZE > 4) {
            self::markTestSkipped('A 32-bit system is required to perform this test.');
        }

        self::convertToBytes('2P');
    }
}
