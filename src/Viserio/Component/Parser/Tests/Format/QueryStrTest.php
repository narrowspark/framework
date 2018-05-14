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

namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\QueryStrDumper;
use Viserio\Component\Parser\Parser\QueryStrParser;

/**
 * @internal
 *
 * @small
 */
final class QueryStrTest extends TestCase
{
    public function testParse(): void
    {
        $parsed = (new QueryStrParser())->parse('status=123&message=hello world');

        self::assertSame(['status' => '123', 'message' => 'hello world'], $parsed);
    }

    public function testDump(): void
    {
        $expected = ['status' => 123, 'message' => 'hello world'];
        $payload = \http_build_query($expected);
        $dump = (new QueryStrDumper())->dump($expected);

        self::assertEquals($payload, $dump);
    }
}
