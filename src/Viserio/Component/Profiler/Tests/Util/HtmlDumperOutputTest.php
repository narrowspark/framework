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

namespace Viserio\Component\Profiler\Tests\Util;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\Util\HtmlDumperOutput;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
