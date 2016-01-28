<?php
namespace Viserio\Filesystem\Tests\Parsers;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Parser\YamlParser;

class YamlParserTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\Parser\YamlParser
     */
    private $parser;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new YamlParser(new Filesystem());
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.yaml')->withContent(
            '
                - escapedCharacters
                - sfComments
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file);

        $this->assertTrue(is_array($parsed));
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
        $file = vfsStream::newFile('temp.yaml')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.yaml.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.yal')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.yal.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file));
    }

    public function testDump()
    {
        # code...
    }
}
