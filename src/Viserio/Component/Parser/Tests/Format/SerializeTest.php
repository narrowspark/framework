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
use Viserio\Component\Parser\Dumper\SerializeDumper;
use Viserio\Component\Parser\Parser\SerializeParser;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class SerializeTest extends TestCase
{
    public function testParse(): void
    {
        $parsed = (new SerializeParser())->parse('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}');

        self::assertSame(['status' => 123, 'message' => 'hello world'], $parsed);
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(\Viserio\Contract\Parser\Exception\ParseException::class);

        (new SerializeParser())->parse('asdgfg<-.<fsdw|df>24hg2=');
    }

    public function testDump(): void
    {
        $dump = (new SerializeDumper())->dump(['status' => 123, 'message' => 'hello world']);

        self::assertEquals('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}', $dump);
    }
}
