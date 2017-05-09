<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\Xml;

class XMLTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\Formats\Xml
     */
    private $parser;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->root   = vfsStream::setup();
        $this->parser = new Xml();
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

        $parsed = $this->parser->parse($file->url());

        self::assertSame(['to' => 'Tove', 'from' => 'Jani', 'heading' => 'Reminder'], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testDump()
    {
        $array = [
            'Good guy' => [
                'name'   => 'Luke Skywalker',
                'weapon' => 'Lightsaber',
            ],
            'Bad guy' => [
                'name'   => 'Sauron',
                'weapon' => 'Evil Eye',
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent(
            '<?xml version="1.0"?>
<root><Good_guy><name>Luke Skywalker</name><weapon>Lightsaber</weapon></Good_guy><Bad_guy><name>Sauron</name><weapon>Evil Eye</weapon></Bad_guy></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent($this->parser->dump($array))->at($this->root);

        self::assertEquals(str_replace("\r\n", '', $this->file->read($file->url())), str_replace("\r\n", '', $this->file->read($dump->url())));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\DumpException
     */
    public function testDumpToThrowException()
    {
        $this->parser->dump(['one', 'two', 'three']);
    }
}
