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
use Viserio\Component\Parser\Dumper\SerializeDumper;
use Viserio\Component\Parser\Parser\SerializeParser;

/**
 * @internal
 *
 * @small
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
