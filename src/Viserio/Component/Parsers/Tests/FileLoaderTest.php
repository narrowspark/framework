<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\TaggableParser;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileLoaderTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\FileLoader
     */
    private $fileloader;

    public function setUp()
    {
        $this->root       = vfsStream::setup();
        $this->fileloader = new FileLoader(new TaggableParser());
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

        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $data);
    }

    public function testLoadWithTagOption()
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

        $data = $this->fileloader->load($file->url(), ['tag' => 'Test']);

        self::assertSame(['Test::a' => 1, 'Test::b' => 2, 'Test::c' => 3, 'Test::d' => 4, 'Test::e' => 5], $data);
    }

    public function testLoadWithGroupOption()
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

        $data = $this->fileloader->load($file->url(), ['group' => 'test']);

        self::assertSame(['test' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]], $data);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Only the options "tag" or "group" is supported.
     */
    public function testLoadWithWrongOption()
    {
        $file = vfsStream::newFile('temp.json')->withContent('')->at($this->root);

        $data = $this->fileloader->load($file->url(), ['foo' => 'Test']);
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
        self::assertSame(self::normalizeDirectorySeparator($file->url()), $exist);

        $exist2 = $this->fileloader->exists($file->url());
        self::assertSame(self::normalizeDirectorySeparator($file->url()), $exist2);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\LoadingException
     * @expectedExceptionMessage File [no/file] not found.
     */
    public function testExistsWithFalsePath()
    {
        $exist = $this->fileloader->exists('no/file');
    }

    public function testExists()
    {
        $file = vfsStream::newFile('temp.json')->withContent('{"a":1 }')->at($this->root);

        $this->fileloader->setDirectories([
            'foo/bar',
            vfsStream::url('root'),
        ]);

        $exist = $this->fileloader->exists('temp.json');

        self::assertSame(self::normalizeDirectorySeparator($file->url()), $exist);
    }

    public function testGetSetAndAddDirectories()
    {
        $this->fileloader->setDirectories([
            'foo/bar/',
            'bar/foo/',
        ]);

        $directory = $this->fileloader->getDirectories();

        self::assertSame('foo/bar/', $directory[0]);
        self::assertSame('bar/foo/', $directory[1]);

        $this->fileloader->addDirectory('added/directory');

        $directory = $this->fileloader->getDirectories();

        self::assertSame('added/directory', $directory[2]);
    }
}
