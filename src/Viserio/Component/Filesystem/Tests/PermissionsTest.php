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

namespace Viserio\Component\Filesystem\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Permissions;

/**
 * @covers \Viserio\Component\Filesystem\Permissions
 *
 * @internal
 *
 * @small
 */
final class PermissionsTest extends TestCase
{
    public function testNumbersPassThrough(): void
    {
        self::assertEquals(0755, Permissions::notation(0755));
        self::assertEquals(493, Permissions::notation(493));
    }

    public function testOctetStringsAreConverted(): void
    {
        self::assertEquals(0755, Permissions::notation('755'));
        self::assertEquals(0755, Permissions::notation('0755'));
        self::assertEquals(04755, Permissions::notation('4755'));
        self::assertEquals(04755, Permissions::notation('04755'));
    }

    public function testFlagStringsAreConverted(): void
    {
        self::assertEquals(0755, Permissions::notation('rwxr-xr-x'));
        self::assertEquals(0264, Permissions::notation('-w-rw-r--'));
    }

    public function testSpecialOctetIsSet(): void
    {
        self::assertEquals(04755, Permissions::notation('rwsr-xr-x'));
        self::assertEquals(02755, Permissions::notation('rwxr-sr-x'));
        self::assertEquals(01755, Permissions::notation('rwxr-xr-t'));
        self::assertEquals(07644, Permissions::notation('rwSr-Sr-T'));
    }

    public function testCharDDoesntMatter(): void
    {
        self::assertEquals(0755, Permissions::notation('drwxr-xr-x'));
        self::assertEquals(0264, Permissions::notation('-w-rw-r--'));

        self::assertEquals(04755, Permissions::notation('drwsr-xr-x'));
        self::assertEquals(02755, Permissions::notation('drwxr-sr-x'));
        self::assertEquals(01755, Permissions::notation('drwxr-xr-t'));
        self::assertEquals(07644, Permissions::notation('drwSr-Sr-T'));
    }
}
