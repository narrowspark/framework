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

use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\ExceptionInfo;

/**
 * @internal
 *
 * @small
 */
final class ExceptionInfoTest extends TestCase
{
    public function testBadError(): void
    {
        $info = ExceptionInfo::generate('test', 666);

        $expected = [
            'id' => 'test',
            'code' => 500,
            'name' => 'Internal Server Error',
            'detail' => 'An error has occurred and this resource cannot be displayed.',
        ];

        self::assertSame($expected, $info);
    }

    public function testHiddenError(): void
    {
        $info = ExceptionInfo::generate('hi', 503);

        $expected = [
            'id' => 'hi',
            'code' => 503,
            'name' => 'Service Unavailable',
            'detail' => 'The server is currently unavailable. It may be overloaded or down for maintenance.',
        ];

        self::assertSame($expected, $info);
    }
}
