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

        $clone = str_cpy($unique);

        self::assertSame($unique, $clone);

        \sodium_memzero($unique);

        self::assertNotSame($unique, $clone);
    }
}
