<?php
namespace Viserio\Parsers\Tests\Formats;

use Viserio\Parsers\Formats\QueryStr;

class QueryStrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\QueryStr
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new QueryStr();
    }

    public function testParse()
    {
        $parsed = $this->parser->parse('status=123&message=hello world');

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['status' => '123', 'message' => 'hello world'], $parsed);
    }

    public function testDump()
    {
        $expected = ['status' => 123, 'message' => 'hello world'];
        $payload = http_build_query($expected);
        $dump = $this->parser->dump($expected);

        $this->assertEquals($payload, $dump);
    }
}
