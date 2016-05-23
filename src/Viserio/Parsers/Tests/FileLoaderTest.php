<?php
namespace Viserio\Parsers\Tests;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\FileLoader;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\TaggableParser;
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
        $this->fileloader = new FileLoader(new TaggableParser(new Filesystem()), []);
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

    public function testLoadwithGroup()
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

        $data = $this->fileloader->load($file->url(), 'Test');

        $this->assertSame(['Test::a' => 1, 'Test::b' => 2, 'Test::c' => 3, 'Test::d' => 4, 'Test::e' => 5], $data);
    }

    public function testExistsWithCache()
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

        $exist = $this->fileloader->exists($file->url());
        $this->assertSame($this->normalizeDirectorySeparator($file->url()), $exist);

        $exist2 = $this->fileloader->exists($file->url());
        $this->assertSame($this->normalizeDirectorySeparator($file->url()), $exist2);
    }

    public function testExistsWithFalsePath()
    {
        $exist = $this->fileloader->exists('no/file');
        $this->assertFalse($exist);

        $file = vfsStream::newFile('temp.json')->withContent('{"a":1 }')->at($this->root);

        $this->fileloader->setDirectories([
            'foo/bar',
            vfsStream::url('root'),
        ]);

        $exist = $this->fileloader->exists('temp.json');
        $this->assertSame($this->normalizeDirectorySeparator($file->url()), $exist);
    }

    public function testGetParser()
    {
        $this->assertInstanceOf(TaggableParser::class, $this->fileloader->getParser());
    }

    public function testGetSetAndAddDirectories()
    {
        $this->fileloader->setDirectories([
            'foo/bar/',
            'bar/foo/',
        ]);

        $directory = $this->fileloader->getDirectories();

        $this->assertSame('foo/bar', $directory[0]);
        $this->assertSame('bar/foo', $directory[1]);

        $this->fileloader->addDirectory('added/directory');

        $directory = $this->fileloader->getDirectories();

        $this->assertSame('added/directory', $directory[2]);
    }
}
