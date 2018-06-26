<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Util;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\Util\HtmlDumperOutput;

/**
 * @internal
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

        static::assertSame($expectedOutput, $htmlDumperOutput->getOutput());
    }

    public function testClear(): void
    {
        $htmlDumperOutput = new HtmlDumperOutput();
        $htmlDumperOutput('first line', 0);
        $htmlDumperOutput('second line', 2);
        $htmlDumperOutput->reset();

        static::assertNull($htmlDumperOutput->getOutput());
    }
}
