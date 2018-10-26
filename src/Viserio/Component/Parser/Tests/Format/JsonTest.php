<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Parser\Exception\DumpException;
use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Parser\Dumper\JsonDumper;
use Viserio\Component\Parser\Parser\JsonParser;

/**
 * @internal
 */
final class JsonTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testParse(): void
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

        $parsed = (new JsonParser())->parse(\file_get_contents($file->url()));

        $this->assertInternalType('array', $parsed);
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
    }

    public function testSetDepthAndOptionsOnJsonParser(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded.');

        $parser = new JsonParser();
        $parser->setDepth(1);
        $parser->setOptions(0);

        $parser->parse('{
    "a":1,
    "b":2,
    "c":3,
    "d":4,
    "e":5
}');
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(ParseException::class);

        (new JsonParser())->parse('nonexistfile');
    }

    public function testDump(): void
    {
        $book = [
            'title'   => 'bar',
            'author'  => 'foo',
            'edition' => 6,
        ];

        $dump = (new JsonDumper())->dump($book);

        $this->assertJsonStringEqualsJsonString('{
    "title": "bar",
    "author": "foo",
    "edition": 6
}', $dump);
    }

    public function testSetDepthAndOptionsOnJsonDumper(): void
    {
        $this->expectException(DumpException::class);
        $this->expectExceptionMessage('JSON dumping failed: Maximum stack depth exceeded.');

        $book = [
            'title'   => [
                'author'  => 'foo',
                'edition' => 6,
            ],
        ];

        $parser = new JsonDumper();
        $parser->setDepth(1);
        $parser->setOptions(0);
        $parser->dump($book);
    }
}
