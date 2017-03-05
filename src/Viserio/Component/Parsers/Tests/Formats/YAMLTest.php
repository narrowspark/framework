<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\YAML;

class YAMLTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\Formats\YAML
     */
    private $parser;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->parser = new YAML();
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

        $parsed = $this->parser->parse((string) $this->file->read($file->url()));

        self::assertTrue(is_array($parsed));
        self::assertSame(['preset' => 'psr2', 'risky' => false, 'linting' => true], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
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

        $this->parser->parse((string) $this->file->read($file->url()));
    }
}
