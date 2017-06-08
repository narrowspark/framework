<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parsers\Dumpers\SerializeDumper;
use Viserio\Component\Parsers\Parsers\SerializeParser;

class SerializeTest extends TestCase
{
    public function testParse()
    {
        $parsed = (new SerializeParser())->parse('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}');

        self::assertTrue(is_array($parsed));
        self::assertSame(['status' => 123, 'message' => 'hello world'], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exceptions\ParseException
     */
    public function testParseToThrowException()
    {
        (new SerializeParser())->parse('asdgfg<-.<fsdw|df>24hg2=');
    }

    public function testDump()
    {
        $dump = (new SerializeDumper())->dump(['status' => 123, 'message' => 'hello world']);

        self::assertEquals('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}', $dump);
    }
}
