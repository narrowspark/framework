<?php
declare(strict_types=1);
namespace Viserio\Parsers\Tests\Formats\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Formats\INI;

class INITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\Formats\INI
     */
    private $parser;

    /**
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->root   = vfsStream::setup();
        $this->parser = new INI();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
one = 1
five = 5
animal = BIRD'
        )->at($this->root);

        $parsed = $this->parser->parse($this->file->read($file->url()));

        self::assertTrue(is_array($parsed));
        self::assertSame(['one' => '1', 'five' => '5', 'animal' => 'BIRD'], $parsed);
    }

    public function testParseWithSection()
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
[main]

explore=true
[main.sub]

[main.sub.sub]
value=5'
        )->at($this->root);

        $parsed = $this->parser->parse($this->file->read($file->url()));

        self::assertTrue(is_array($parsed));
        self::assertSame(
            ['main' => ['explore' => '1'], 'main.sub' => [], 'main.sub.sub' => ['value' => '5']],
            $parsed
        );
    }

    /**
     * @expectedException \Viserio\Contracts\Parsers\Exception\ParseException
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testDump()
    {
        $dump = $this->parser->dump(['test' => ['value' => true, 'five' => 5]]);
        $file = vfsStream::newFile('temp.ini')->withContent(
'[test]
value=true
five=5')->at($this->root);

        self::assertEquals(preg_replace('/^\s+|\n|\r|\s+$/m', '', $this->file->read($file->url())), preg_replace('/^\s+|\n|\r|\s+$/m', '', $dump));
    }
}
