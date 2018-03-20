<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\TomlDumper;
use Viserio\Component\Parser\Parser\TomlParser;

class TomlTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testParses(): void
    {
        $file = vfsStream::newFile('temp.toml')->withContent(
            "
                backspace = 'This string has a \b backspace character.'
            "
        )->at($this->root);

        $parsed = (new TomlParser())->parse(\file_get_contents($file->url()));

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

    public function testDumpArrayToToml(): void
    {
        $file = \dirname(__DIR__) . '/Fixtures/dumped.toml';

        self::assertSame(
            \str_replace("\r", '', \file_get_contents($file)),
            (new TomlDumper())->dump((new TomlParser())->parse(\file_get_contents($file)))
        );
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parser\Exception\DumpException
     * @expectedExceptionMessage Data type not supporter at the key
     */
    public function testDumperToThrowException(): void
    {
        (new TomlDumper())->dump(['das' => new TomlDumper()]);
    }
}
