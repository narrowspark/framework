<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\Formats\YAML;

class YAMlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\Formats\YAML
     */
    private $format;

    public function setUp()
    {
        $this->parser = new YAML();
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

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['preset' => 'psr2', 'risky' => false, 'linting' => true], $parsed);
    }

    /**
     * @expectedException League\Flysystem\FileNotFoundException
     * #@expectedExceptionMessage
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testSupports()
    {
        $file = vfsStream::newFile('temp.yaml')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.yaml.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.yml')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.yml.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file->url()));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file->url()));
    }

    public function testDump()
    {
        # code...
    }
}
