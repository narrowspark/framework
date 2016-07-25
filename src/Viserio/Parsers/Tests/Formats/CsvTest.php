<?php

declare(strict_types=1);
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Formats\Csv;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\Formats\Csv
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
        $this->parser = new Csv();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.csv')->withContent(
            '
"foo"; "bar"
#"bar"; "foo"
"incorrect"; "number"; "columns"; "will"; "be"; "ignored"
"incorrect"'
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['foo' => 'bar'], $parsed);
    }

    public function testParseWithValidCsv()
    {
        $file = vfsStream::newFile('temp.csv')->withContent(
            '
foo;bar
bar;"foo
foo"
"foo;foo";bar'
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(
            ['foo' => 'bar', 'bar' => 'foo
foo', 'foo;foo' => 'bar'],
            $parsed
        );
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
        $dump = $this->parser->dump(['foo' => 'bar', 'bar' => 'foo
foo', 'foo;foo' => 'bar']);
        $file = vfsStream::newFile('temp.csv')->withContent(
            '
foo;bar
bar;"foo
foo"
"foo;foo";bar')->at($this->root);

        $this->assertEquals(
            preg_replace(
                '/^\s+|\n|\r|\s+$/m',
                '',
                $this->file->read($file->url())
            ),
            preg_replace('/^\s+|\n|\r|\s+$/m', '', $dump)
        );
    }
}
