<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\Util;

use Viserio\WebProfiler\Util\HtmlDumperOutput;

class HtmlDumperOutputTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $htmlDumperOutput = new HtmlDumperOutput();
        $htmlDumperOutput('first line', 0);
        $htmlDumperOutput('second line', 2);
        $expectedOutput = <<<string
first line
    second line

string;

        static::assertSame($expectedOutput, $htmlDumperOutput->getOutput());
    }

    public function testClear()
    {
        $htmlDumperOutput = new HtmlDumperOutput();
        $htmlDumperOutput('first line', 0);
        $htmlDumperOutput('second line', 2);
        $htmlDumperOutput->flush();

        static::assertNull($htmlDumperOutput->getOutput());
    }
}
