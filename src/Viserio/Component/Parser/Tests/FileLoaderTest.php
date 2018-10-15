<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Parser\Exception\FileNotFoundException;
use Viserio\Component\Contract\Parser\Exception\NotSupportedException;
use Viserio\Component\Parser\FileLoader;

/**
 * @internal
 */
final class FileLoaderTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parser\FileLoader
     */
    private $fileloader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root       = vfsStream::setup();
        $this->fileloader = new FileLoader();
    }

    public function testLoad(): void
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

        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $data);
    }

    public function testLoadWithTagOption(): void
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

        static::assertSame(['Test::a' => 1, 'Test::b' => 2, 'Test::c' => 3, 'Test::d' => 4, 'Test::e' => 5], $data);
    }

    public function testLoadWithGroupOption(): void
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

        static::assertSame(['test' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]], $data);
    }

    public function testLoadWithWrongOption(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Only the options "tag" and "group" are supported.');

        $file = vfsStream::newFile('temp.json')->withContent('')->at($this->root);

        $this->fileloader->load($file->url(), ['foo' => 'Test']);
    }

    public function testExistsWithCache(): void
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
        static::assertSame($file->url(), $exist);

        $exist2 = $this->fileloader->exists($file->url());
        static::assertSame($file->url(), $exist2);
    }

    public function testExistsWithFalsePath(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [no' . \DIRECTORY_SEPARATOR . 'file] not found.');

        $this->fileloader->exists('no' . \DIRECTORY_SEPARATOR . 'file');
    }

    public function testExists(): void
    {
        $file = vfsStream::newFile('temp.json')->withContent('{"a":1 }')->at($this->root);

        $this->fileloader->setDirectories([
            'foo/bar',
            vfsStream::url('root'),
        ]);

        $exist = $this->fileloader->exists('temp.json');

        static::assertSame($file->url(), $exist);
    }

    public function testGetSetAndAddDirectories(): void
    {
        $this->fileloader->setDirectories([
            'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR,
            'bar' . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR,
        ]);

        $directory = $this->fileloader->getDirectories();

        static::assertSame('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR, $directory[0]);
        static::assertSame('bar' . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR, $directory[1]);

        $this->fileloader->addDirectory('added' . \DIRECTORY_SEPARATOR . 'directory');

        $directory = $this->fileloader->getDirectories();

        static::assertSame('added' . \DIRECTORY_SEPARATOR . 'directory', $directory[2]);
    }
}
