<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Stream;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Stream\MutableFile;

class MutableFileTest extends TestCase
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

    public function testWrite(): void
    {
        $filename = vfsStream::newFile('mutable.txt')
            ->at($this->root)
            ->url();

        $fStream = new MutableFile($filename);
        $length  = 65537;

        self::assertSame($length, $fStream->write(\random_bytes($length)));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\OutOfBoundsException
     * @expectedExceptionMessage Length parameter cannot be negative.
     */
    public function testWriteThrowsOnNegativeLength(): void
    {
        $filename = vfsStream::newFile('mutable_throw.txt')
            ->at($this->root)
            ->url();

        $fStream = new MutableFile($filename);

        $fStream->write('te', -1);
    }

    public function testRead(): void
    {
        $filename = vfsStream::newFile('mutable_throw.txt')
            ->at($this->root)
            ->url();
        $fStream = new MutableFile($filename);

        self::assertSame('Mutable file cannot be read.', $fStream->read(0));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException
     * @expectedExceptionMessage Please choose a writable mode [w, w+, rw, r+, x+, c+, wb, w+b, r+b, x+b, c+b, w+t, r+t, x+t, c+t, a, a+] for your resource.
     */
    public function testConstructorTestForWritableMode(): void
    {
        $filename = vfsStream::newFile('mutable_throw.txt')
            ->at($this->root)
            ->url();

        new MutableFile(\fopen($filename, 'rb+'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\UnexpectedValueException
     * @expectedExceptionMessage Invalid stream provided; must be a filename or stream resource.
     */
    public function testConstructorToThrowExceptionForNoFileOrStream(): void
    {
        new MutableFile(1);
    }
}
