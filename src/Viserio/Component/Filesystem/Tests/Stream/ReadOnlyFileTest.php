<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Stream;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Filesystem\Stream\ReadOnlyFile;

class ReadOnlyFileTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * Setup the environment.
     */
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testGetHashWithEmptyKey(): void
    {
        $buf      = \random_bytes(65537);
        $filename = vfsStream::newFile('temp.txt')
            ->withContent($buf)
            ->at($this->root)
            ->url();

        $fileOne = new ReadOnlyFile($filename);

        $fp      = \fopen($filename, 'rb');
        $fileTwo = new ReadOnlyFile($fp);

        self::assertSame(
            $fileOne->getHash(),
            $fileTwo->getHash()
        );

        \fclose($fp);
    }

    public function testGetHashWithKey(): void
    {
        $buf      = \random_bytes(65537);
        $filename = vfsStream::newFile('temp.txt')
            ->withContent($buf)
            ->at($this->root)
            ->url();

        $key      = KeyFactory::generateKey();
        $fileOne  = new ReadOnlyFile($filename, $key);

        $fp      = \fopen($filename, 'rb');
        $fileTwo = new ReadOnlyFile($fp, $key);

        self::assertSame(
            $fileOne->getHash(),
            $fileTwo->getHash()
        );

        \fclose($fp);
    }

    public function testRead(): void
    {
        $buf      = \random_bytes(65537);
        $filename = vfsStream::newFile('temp.txt')
            ->withContent($buf)
            ->at($this->root)
            ->url();

        $fStream = new ReadOnlyFile($filename);

        self::assertSame(65537, $fStream->getSize());
        self::assertSame($fStream->read(65537), $buf);
    }

    public function testTell(): void
    {
        $buf      = \random_bytes(65537);
        $filename = vfsStream::newFile('temp.txt')
            ->withContent($buf)
            ->at($this->root)
            ->url();

        $fStream = new ReadOnlyFile($filename);

        $fStream->seek(65537);

        self::assertSame(65537, $fStream->tell());

        $fStream->seek(0);

        self::assertSame(0, $fStream->tell());
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\RuntimeException
     * @expectedExceptionMessage Unable to seek to stream position -1 with whence SEEK_SET.
     */
    public function testSeekToThrowException(): void
    {
        $filename = vfsStream::newFile('temp.txt')
            ->at($this->root)
            ->url();

        $fStream = new ReadOnlyFile($filename);

        $fStream->seek(-1);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\OutOfBoundsException
     * @expectedExceptionMessage Length parameter cannot be negative.
     */
    public function testReadThrowExceptionOnNegativeLength(): void
    {
        $filename = vfsStream::newFile('temp.txt')
            ->at($this->root)
            ->url();

        $fStream = new ReadOnlyFile($filename);

        $fStream->read(-1);
    }

    public function testReadWithZeroLength(): void
    {
        $filename = vfsStream::newFile('temp.txt')
            ->at($this->root)
            ->url();

        $fStream = new ReadOnlyFile($filename);

        self::assertSame('', $fStream->read(0));
    }

    public function testGetRemainingBytes(): void
    {
        $buf      = \random_bytes(65537);
        $filename = vfsStream::newFile('temp.txt')
            ->withContent($buf)
            ->at($this->root)
            ->url();

        $fStream = new ReadOnlyFile($filename);

        self::assertSame(65537, $fStream->getRemainingBytes());

        $fStream->read(32768);

        self::assertSame(32769, $fStream->getRemainingBytes());
    }

    /**
     * Test for Time-of-check Time-of-use (TOCTOU) attacks (race conditions).
     *
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileModifiedException
     * @expectedExceptionMessage Read-only file has been modified since it was opened for reading.
     */
    public function testReadToThrowException(): void
    {
        $filename = vfsStream::newFile('temp.txt')->at($this->root)->url();

        $buf = \random_bytes(65537);

        \file_put_contents($filename, $buf);

        $fStream = new ReadOnlyFile($filename);

        $fStream->read(65537);
        $fStream->seek(0);

        \file_put_contents(
            $filename,
            \mb_substr($buf, 0, 32768, '8bit') . 'x' . \mb_substr($buf, 32768, null, '8bit')
        );

        $fStream->read(65537);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException
     * @expectedExceptionMessage This is a read-only file handle.
     */
    public function testWrite(): void
    {
        $filename = vfsStream::newFile('temp.txt')->at($this->root)->url();

        $fStream = new ReadOnlyFile($filename);

        $fStream->write('');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\OutOfBoundsException
     * @expectedExceptionMessage Out-of-bounds read.
     */
    public function testReadThrowsOutOfBoundsException(): void
    {
        $filename = vfsStream::newFile('temp.txt')
            ->withContent('test')
            ->at($this->root)
            ->url();

        $fStream = new ReadOnlyFile($filename);

        $fStream->read(5);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException
     * @expectedExceptionMessage Please choose a readable mode [r, w+, r+, x+, c+, rb, w+b, r+b, x+b, c+b, rt, w+t, r+t, x+t, c+t, a+] for your resource.
     */
    public function testConstructorToThrowExceptionForNoReadableMode(): void
    {
        $filename = vfsStream::newFile('throw.txt')
            ->at($this->root)
            ->url();

        new ReadOnlyFile(\fopen($filename, 'wb+'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\UnexpectedValueException
     * @expectedExceptionMessage Invalid stream provided; must be a filename or stream resource.
     */
    public function testConstructorToThrowExceptionForNoFileOrStream(): void
    {
        new ReadOnlyFile(1);
    }
}
