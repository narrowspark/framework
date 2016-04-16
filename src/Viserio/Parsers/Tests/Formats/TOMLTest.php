<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\Formats\TOML;

class TOMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\Formats\TOML
     */
    private $format;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new TOML();
    }

    public function testParses()
    {
        $file = vfsStream::newFile('temp.toml')->withContent(
            "
                backspace = 'This string has a \b backspace character.'
            "
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['backspace' => 'This string has a \b backspace character.'], $parsed);
    }

    /**
     * @expectedException League\Flysystem\FileNotFoundException
     * #@expectedExceptionMessage
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testDump()
    {
        # code...
    }
}
