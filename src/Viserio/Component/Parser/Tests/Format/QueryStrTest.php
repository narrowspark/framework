<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\QueryStrDumper;
use Viserio\Component\Parser\Parser\QueryStrParser;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
