<?php
namespace Viserio\Filesystem\Tests\Parsers;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Parser\IniParser;

class TomlParserTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

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

        $parsed = $this->parser->parse($file);

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['backspace' => 'This string has a \b backspace character.'], $parsed);
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\LoadingException
     * #@expectedExceptionMessage
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testSupport()
    {
        $file = vfsStream::newFile('temp.toml')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.toml.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file));
    }

    public function testDump()
    {
        # code...
    }
}
