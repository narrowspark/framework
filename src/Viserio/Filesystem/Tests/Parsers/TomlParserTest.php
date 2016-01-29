<?php
namespace Viserio\Filesystem\Tests\Parsers;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parsers\TomlParser;

class TomlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\Parser\TomlParser
     */
    private $parser;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new TomlParser(new Filesystem());
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

    public function testParseGroup()
    {
        $file = vfsStream::newFile('temp.toml')->withContent(
            "
                backspace = 'This string has a \b backspace character.'
            "
        )->at($this->root);

        $parsed = $this->parser->parse($file->url(), 'foo');

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['foo::backspace' => 'This string has a \b backspace character.'], $parsed);
    }

    /**
     * @expectedException League\Flysystem\FileNotFoundException
     * #@expectedExceptionMessage
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testSupports()
    {
        $file = vfsStream::newFile('temp.toml')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.toml.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file->url()));
    }

    public function testDump()
    {
        # code...
    }
}
