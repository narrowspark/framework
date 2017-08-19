<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests;

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * Verify that safeStrcpy() doesn't fall prey to interned strings.
     *
     * @covers Util::safeStrcpy()
     */
    public function testSafeStrcpy()
    {
        //var_dump(get_extension_funcs('sodium'));die;
        $unique = \random_bytes(128);

        $clone = \safe_str_cpy($unique);

        self::assertSame($unique, $clone);

        \sodium_memzero($unique);

        self::assertNotSame($unique, $clone);
    }

    /**
     * Test our HKDF-esque construct built atop BLAKE2b
     */
    public function testBlake2bKDF()
    {
        $ikm = 'YELLOW SUBMARINE';
        $len = 32;
        $info = 'TESTING HKDF-BLAKE2B';
        $salt = str_repeat("\x80", 32);

        $test = \hash_hkdf_blake2b($ikm, $len, $info, $salt);

        self::assertSame(
            $test,
            "\x7b\xaf\xb1\x11\x1c\xda\xce\x81\xd1\xb0\x73\xff\x6e\x68\x8f\xc3".
            "\x6f\xb5\xa2\xc7\xbd\x53\xf6\xf1\xb4\x2f\x80\x71\x29\x4b\xb7\xf7"
        );
        // Let's change the IKM
        $ikmB = 'YELLOW SUBMARINF';
        $testIkm = \hash_hkdf_blake2b($ikmB, $len, $info, $salt);

        self::assertNotEquals($test, $testIkm);

        // Let's change the info
        $infoB = 'TESTING HKDF-BLAKE2C';
        $testInfo = \hash_hkdf_blake2b($ikm, $len, $infoB, $salt);

        self::assertNotEquals($test, $testInfo);

        // Let's change the salt
        $saltB = str_repeat("\x80", 31) . "\x81";
        $testSalt = \hash_hkdf_blake2b($ikm, $len, $info, $saltB);

        self::assertNotEquals($test, $testSalt);
    }
}
