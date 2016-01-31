<?php
namespace Viserio\Filesystem\Tests;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\FileLoader;
use Viserio\Filesystem\Parsers\IniParser;

class FileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\FileLoader
     */
    private $fileloader;

    public function setUp()
    {
        $this->root       = vfsStream::setup();
        $this->fileloader = new FileLoader(new Filesystem(), __DIR__.'/Fixture');
    }

    public function testLoad()
    {
        # code...
    }

    public function testExists()
    {
        # code...
    }

    public function testCascadePackage()
    {
        # code...
    }

    public function testNamespace()
    {
        $this->fileloader->addNamespace('foo', 'barr');

        $this->assertSame('barr', $this->fileloader->getNamespaces('foo'));
    }

    public function testParser()
    {
        $this->fileloader->addNamespace('ini.dist', new IniParser(new Filesystem()));

        $this->assertEquals([
            'ini',
            'json',
            'php',
            'toml',
            'xml',
            'yaml',
            'ini.dist',
        ], $this->fileloader->getParsers());
    }
}
