<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\TOML;

class TOMLTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\Formats\TOML
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
        $this->parser = new TOML();
    }

    public function testParses()
    {
        $file = vfsStream::newFile('temp.toml')->withContent(
            "
                backspace = 'This string has a \b backspace character.'
            "
        )->at($this->root);

        $parsed = $this->parser->parse($this->file->read($file->url()));

        self::assertTrue(is_array($parsed));
        self::assertSame(['backspace' => 'This string has a \b backspace character.'], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }
}
