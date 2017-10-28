<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parser\Parser\TomlParser;

class TomlTest extends TestCase
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
        $this->file = new Filesystem();
        $this->root = vfsStream::setup();
    }

    public function testParses(): void
    {
        $file = vfsStream::newFile('temp.toml')->withContent(
            "
                backspace = 'This string has a \b backspace character.'
            "
        )->at($this->root);

        $parsed = (new TomlParser())->parse((string) $this->file->read($file->url()));

        self::assertTrue(\is_array($parsed));
        self::assertSame(['backspace' => 'This string has a \b backspace character.'], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parser\Exception\ParseException
     * @expectedExceptionMessage Unable to parse the TOML string.
     */
    public function testParseToThrowException(): void
    {
        (new TomlParser())->parse('nonexistfile');
    }
}
