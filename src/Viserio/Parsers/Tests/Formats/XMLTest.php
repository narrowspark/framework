<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\Formats\XML;
use Viserio\Filesystem\Filesystem;

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

    /**
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file = new Filesystem();
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
     * @expectedException Viserio\Contracts\Parsers\Exception\ParseException
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
