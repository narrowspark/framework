<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests;

use ParagonIE\ConstantTime\Base64UrlSafe;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Encryption\HiddenString;

/**
 * @backupGlobals disabled
 */
class HiddenStringTest extends TestCase
{
    /**
     * @param bool $disallowInline
     * @param bool $disallowSerialization
     *
     * @dataProvider hiddenStringValueDataProvider
     */
    public function testHiddenStringOutput(bool $disallowInline, bool $disallowSerialization)
    {
        $str = Base64UrlSafe::encode(\random_bytes(32));

        $hidden = new HiddenString($str, $disallowInline, $disallowSerialization);

        ob_start();
        var_dump($hidden);
        $dump = ob_get_clean();

        self::assertFalse(\mb_strpos($dump, $str));

        $print = \print_r($hidden, true);

        self::assertFalse(\mb_strpos($print, $str));

        $cast = (string) $hidden;

        if ($disallowInline) {
            self::assertFalse(\mb_strpos($cast, $str));
        } else {
            self::assertNotFalse(\mb_strpos($cast, $str));
        }

        $serial = \serialize($hidden);

        if ($disallowSerialization) {
            self::assertFalse(\mb_strpos($serial, $str));
        } else {
            self::assertNotFalse(\mb_strpos($serial, $str));
        }
    }

    public function hiddenStringValueDataProvider()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }
}
