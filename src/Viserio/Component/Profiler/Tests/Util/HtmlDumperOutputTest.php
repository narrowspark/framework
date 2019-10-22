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

namespace Viserio\Component\Profiler\Tests\Util;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\Util\HtmlDumperOutput;

/**
 * @internal
 *
 * @small
 */
final class HtmlDumperOutputTest extends TestCase
{
    public function testOutput(): void
    {
        $htmlDumperOutput = new HtmlDumperOutput();
        $htmlDumperOutput('first line', 0);
        $htmlDumperOutput('second line', 2);
        $expectedOutput = <<<'string'
first line
    second line

string;

        self::assertSame($expectedOutput, $htmlDumperOutput->getOutput());
    }

    public function testClear(): void
    {
        $htmlDumperOutput = new HtmlDumperOutput();
        $htmlDumperOutput('first line', 0);
        $htmlDumperOutput('second line', 2);
        $htmlDumperOutput->reset();

        self::assertNull($htmlDumperOutput->getOutput());
    }
}
