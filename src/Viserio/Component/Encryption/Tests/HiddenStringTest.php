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
    public function testHiddenStringOutput()
    {
        $str = Base64UrlSafe::encode(\random_bytes(32));

        $hidden = new HiddenString($str);

        ob_start();
        var_dump($hidden);
        $dump = ob_get_clean();

        self::assertFalse(\mb_strpos($dump, $str));

        $print = \print_r($hidden, true);

        self::assertFalse(\mb_strpos($print, $str));

        $cast = (string) $hidden;

        self::assertFalse(\mb_strpos($cast, $str));

        $serial = \serialize($hidden);

        self::assertFalse(\mb_strpos($serial, $str));
    }
}
