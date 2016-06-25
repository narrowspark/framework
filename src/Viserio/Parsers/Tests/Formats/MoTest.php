<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Formats\Mo;

class MoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\Mo
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new Mo();
    }

    public function testParse()
    {
        $parsed = $this->parser->parse(__DIR__.'/stubs/resources.mo');

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['foo' => 'bar'], $parsed);
    }

    public function testParseWithPlurals()
    {
        $parsed = $this->parser->parse(__DIR__.'/stubs/plurals.mo');

        $this->assertTrue(is_array($parsed));
        $this->assertSame(
            ['foo' => 'bar', 'foos' => '{0} bar|{1} bars'],
            $parsed
        );
    }

    /**
     * @expectedException Viserio\Contracts\Parsers\Exception\ParseException
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testDump()
    {
        $dump = $this->parser->dump(['foo' => 'bar']);
        $this->assertStringEqualsFile(__DIR__.'/stubs/resources.mo', $dump);
    }
}
