<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Parsers\YamlParser;

class YamlTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->root   = vfsStream::setup();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.yaml')->withContent(
            '
preset: psr2

risky: false

linting: true
            '
        )->at($this->root);

        $parsed = (new YamlParser())->parse((string) $this->file->read($file->url()));

        self::assertTrue(is_array($parsed));
        self::assertSame(['preset' => 'psr2', 'risky' => false, 'linting' => true], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exceptions\ParseException
     * @expectedExceptionMessage Unable to parse at line 3 (near "  foo: bar").
     */
    public function testParseToThrowException()
    {
        $file = vfsStream::newFile('temp.yaml')->withContent(
            '
collection:
-  key: foo
  foo: bar
            '
        )->at($this->root);

        (new YamlParser())->parse((string) $this->file->read($file->url()));
    }
}
