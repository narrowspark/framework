<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
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
    private $parser;

    /**
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file = new Filesystem();
        $this->root = vfsStream::setup();
        $this->parser = new XML();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.xml')->withContent(
            '<?xml version="1.0"?>
<data>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
</data>
            '
        )->at($this->root);

        $parsed = $this->parser->parse($this->file->read($file->url()));

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
        $array = [
            'Good guy' => [
                'name' => 'Luke Skywalker',
                'weapon' => 'Lightsaber',
            ],
            'Bad guy' => [
                'name' => 'Sauron',
                'weapon' => 'Evil Eye',
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent(
            '<?xml version="1.0"?>
<root><Good_guy><name>Luke Skywalker</name><weapon>Lightsaber</weapon></Good_guy><Bad_guy><name>Sauron</name><weapon>Evil Eye</weapon></Bad_guy></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent($this->parser->dump($array))->at($this->root);

        $this->assertEquals($this->file->read($file->url()), $this->file->read($dump->url()));
    }

    public function testDumpToThrowException()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('@TODO find error.');
        }

        $this->expectException('Viserio\Contracts\Parsers\Exception\DumpException');
        $this->parser->dump(['one', 'two', 'three']);
    }
}
