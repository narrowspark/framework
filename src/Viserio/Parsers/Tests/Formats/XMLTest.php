<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\Formats\XML;

class XMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\Formats\XML
     */
    private $format;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new XML();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.xml')->withContent(
            '<?xml version="1.0"?>
<note>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
</note>
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['to' => 'Tove', 'from' => 'Jani', 'heading' => 'Reminder'], $parsed);
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
        $file = vfsStream::newFile('temp.xml')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.xml.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file->url()));
    }

    public function testDump()
    {
        # code...
    }
}
