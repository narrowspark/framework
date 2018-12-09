<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\TomlDumper;
use Viserio\Component\Parser\Parser\TomlParser;

/**
 * @internal
 */
final class TomlTest extends TestCase
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

    public function testParses(): void
    {
        $file = vfsStream::newFile('temp.toml')->withContent(
            "
                backspace = 'This string has a \\b backspace character.'
            "
        )->at($this->root);

        $parsed = (new TomlParser())->parse(\file_get_contents($file->url()));

        $this->assertIsArray($parsed);
        $this->assertSame(['backspace' => 'This string has a \b backspace character.'], $parsed);
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\ParseException::class);
        $this->expectExceptionMessage('Unable to parse the TOML string.');

        (new TomlParser())->parse('nonexistfile');
    }

    public function testDumpArrayToToml(): void
    {
        $file = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'dumped.toml';

        $this->assertSame(
            \str_replace("\r", '', \file_get_contents($file)),
            (new TomlDumper())->dump((new TomlParser())->parse(\file_get_contents($file)))
        );
    }

    public function testDumperToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\DumpException::class);
        $this->expectExceptionMessage('Data type not supporter at the key');

        (new TomlDumper())->dump(['das' => new TomlDumper()]);
    }
}
