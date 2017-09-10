<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parser\Dumper\XmlDumper;
use Viserio\Component\Parser\Parser\XmlParser;

class XmlTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Contract\Filesystem\Filesystem
     */
    private $file;

    public function setUp(): void
    {
        $this->file   = new Filesystem();
        $this->root   = vfsStream::setup();
    }

    public function testParse(): void
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

        $parsed = (new XmlParser())->parse((string) $this->file->read($file->url()));

        self::assertSame(['to' => 'Tove', 'from' => 'Jani', 'heading' => 'Reminder'], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parser\Exception\ParseException
     * @expectedExceptionMessage [ERROR 4] Start tag expected, '<' not found (in n/a - line 1, column 1)
     */
    public function testParseToThrowException(): void
    {
        (new XmlParser())->parse('nonexistfile');
    }

    public function testDump(): void
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
'
        )->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        self::assertEquals(\str_replace("\r\n", '', $this->file->read($file->url())), \str_replace("\r\n", '', $this->file->read($dump->url())));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parser\Exception\DumpException
     */
    public function testDumpToThrowException(): void
    {
        (new XmlDumper())->dump(['one', 'two', 'three']);
    }
}
