<?php

declare(strict_types=1);
namespace Viserio\Parsers\Tests\Formats;

use Viserio\Parsers\Formats\Serialize;

class SerializeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\Serialize
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new Serialize();
    }

    public function testParse()
    {
        $parsed = $this->parser->parse('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}');

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['status' => 123, 'message' => 'hello world'], $parsed);
    }

    /**
     * @expectedException Viserio\Contracts\Parsers\Exception\ParseException
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('asdgfg<-.<fsdw|df>24hg2=');
    }

    public function testDump()
    {
        $dump = $this->parser->dump(['status' => 123, 'message' => 'hello world']);
        $this->assertEquals('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}', $dump);
    }
}
