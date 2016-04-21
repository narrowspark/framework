<?php
namespace Viserio\Parsers\Tests\Formats\Formats;

use Viserio\Parsers\Formats\BSON;

class BSONTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\BSON
     */
    private $parser;

    public function setUp()
    {
        if (!function_exists('bson_decode')) {
            $this->markTestSkipped('Failed To Parse BSON - Supporting Library Not Available');
        }

        $this->parser = new BSON();
    }

    public function testParse()
    {
        $expected = ['status' => 123, 'message' => 'hello world'];
        $payload  = bson_encode($expected);
        $parsed   = $this->parser->parse($payload);

        $this->assertTrue(is_array($parsed));
        $this->assertSame($expected, $parsed);
    }

    /**
     * @expectedException Viserio\Contracts\Parsers\Exception\ParseException
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('asdgfg<<-.<fsdw|df>24hg2=');
    }

    public function testDump()
    {
        $expected = ['status' => 123, 'message' => 'hello world'];
        $payload  = bson_encode($expected);
        $dump     = $this->parser->dump($expected);

        $this->assertEquals($payload, $dump);
    }

    /**
     * @expectedException Viserio\Contracts\Parsers\Exception\DumpException
     */
    public function testDumpToThrowException()
    {
        $this->parser->dump('asdgfg<<-.<fsdw|df>24hg2=');
    }
}
