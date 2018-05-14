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

namespace Viserio\Component\Exception\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\ExceptionIdentifier;

/**
 * @internal
 *
 * @small
 */
final class ExceptionIdentifierTest extends TestCase
{
    public function testIdentifyOne(): void
    {
        $e = new Exception();

        self::assertSame(ExceptionIdentifier::identify($e), ExceptionIdentifier::identify($e));
    }

    public function testIdentifyTwo(): void
    {
        $first = new Exception();
        $second = new Exception();

        self::assertSame(ExceptionIdentifier::identify($first), ExceptionIdentifier::identify($first));
        self::assertSame(ExceptionIdentifier::identify($second), ExceptionIdentifier::identify($second));
        self::assertNotSame(ExceptionIdentifier::identify($first), ExceptionIdentifier::identify($second));
    }

    public function testIdentifyMany(): void
    {
        $arr = [];

        for ($j = 0; $j < 20; $j++) {
            $arr[] = new Exception();
        }

        $ids = [];

        foreach ($arr as $e) {
            $ids[] = ExceptionIdentifier::identify($e);
        }

        // these should have been cleared
        self::assertNotSame(ExceptionIdentifier::identify($arr[0]), $ids[0]);
        self::assertNotSame(ExceptionIdentifier::identify($arr[2]), $ids[2]);
        self::assertNotSame(ExceptionIdentifier::identify($arr[5]), $ids[5]);

        // these should still be in memory
        self::assertSame(ExceptionIdentifier::identify($arr[7]), $ids[7]);
        self::assertSame(ExceptionIdentifier::identify($arr[15]), $ids[15]);
    }
}
