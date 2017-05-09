<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\Json;

class JsonTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\Formats\Json
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
        $this->parser = new Json();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.json')->withContent(
            '
{
    "a":1,
    "b":2,
    "c":3,
    "d":4,
    "e":5
}
            '
        )->at($this->root);

        $parsed = $this->parser->parse((string) $this->file->read($file->url()));

        self::assertTrue(is_array($parsed));
        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
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
        $book = [
            'title'   => 'bar',
            'author'  => 'foo',
            'edition' => 6,
        ];

        $dump = $this->parser->dump($book);

        self::assertJsonStringEqualsJsonString('{
    "title": "bar",
    "author": "foo",
    "edition": 6
}', $dump);
    }
}
