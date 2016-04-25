<?php
namespace Viserio\Filesystem\Tests;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\FileLoader;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Parser;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileLoaderTest extends \PHPUnit_Framework_TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

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
        $this->fileloader = new FileLoader(new Parser(new Filesystem()), []);
    }

    public function testLoad()
    {
        $file = vfsStream::newFile('temp.json')->withContent(
            '
{
    "a":1,
    "b":2,
    "c":3,
    "d":4,
    "e":5
}
            '
        )->at($this->root);

        $data = $this->fileloader->load($file->url());

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $data);
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\UnsupportedFormatException
     * @expectedExceptionMessage Unable to find the right Parser for [inia].
     */
    public function testLoadToThrowException()
    {
        $this->fileloader->load('test.inia');
    }

    public function testLoadwithGroup()
    {
        $data = $this->fileloader->load('test.ini', 'Test');

        $this->assertSame(['Test::one' => '1', 'Test::five' => '5', 'Test::animal' => 'BIRD'], $data);
    }

    public function testExistswithEnvironment()
    {
        $exist = $this->fileloader->exists('test.ini', null, 'production', null);
        $this->assertSame($this->normalizeDirectorySeparator(__DIR__ . '/Fixture/production/test.ini'), $exist);
    }

    public function testExistsWithCache()
    {
        $exist = $this->fileloader->exists('test.json');
        $this->assertSame($this->normalizeDirectorySeparator(__DIR__ . '/Fixture/test.json'), $exist);

        $exist2 = $this->fileloader->exists('test.json');
        $this->assertSame($this->normalizeDirectorySeparator(__DIR__ . '/Fixture/test.json'), $exist2);

        $envExist1 = $this->fileloader->exists('test.ini', null, 'production', null);
        $this->assertSame($this->normalizeDirectorySeparator(__DIR__ . '/Fixture/production/test.ini'), $envExist1);

        $envExist2 = $this->fileloader->exists('test.ini', null, 'production', null);
        $this->assertSame($this->normalizeDirectorySeparator(__DIR__ . '/Fixture/production/test.ini'), $envExist2);
    }

    public function testCascadePackage()
    {
        # code...
    }

    public function testNamespace()
    {
        $this->fileloader->addNamespace('foo', 'barr');

        $this->assertContains('barr', $this->fileloader->getNamespaces());
    }

    public function testGetParser()
    {
        $this->assertInstanceOf(Parser::class, $this->fileloader->getParser());
    }
}
