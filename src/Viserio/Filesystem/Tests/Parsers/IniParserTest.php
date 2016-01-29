<?php
namespace Viserio\Filesystem\Tests\Parsers;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Parsers\IniParser;

class IniParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\Parser\IniParser
     */
    private $parser;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new IniParser(new Filesystem());
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
                ; This is a sample configuration file
                ; Comments start with ";", as in php.ini

                one = 1
                five = 5
                animal = BIRD
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
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
        $file = vfsStream::newFile('temp.ini')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.ini.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file->url()));
    }

    public function testDump()
    {
        # code...
    }
}
