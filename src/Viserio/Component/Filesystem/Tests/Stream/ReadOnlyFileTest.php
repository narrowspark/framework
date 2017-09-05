<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Stream;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Filesystem\Stream\ReadOnlyFile;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class ReadOnlyFileTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \org\bovigo\vfs\vfsStream
     */
    private $root;

    /**
     * Setup the environment.
     */
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testGetHashWithEmptyKey()
    {
        $filename = vfsStream::newFile('temp.txt')->at($this->root)->url();

        \file_put_contents($filename, \random_bytes(65537));

        $fileOne = new ReadOnlyFile($filename);

        $fp      = \fopen($filename, 'rb');
        $fileTwo = new ReadOnlyFile($fp);

        self::assertSame(
            $fileOne->getHash(),
            $fileTwo->getHash()
        );

        \fclose($fp);
    }

    public function testGetHashWithKey()
    {
        $filename = vfsStream::newFile('temp.txt')->at($this->root)->url();

        \file_put_contents($filename, \random_bytes(65537));

        $password = \random_bytes(32);
        $key      = KeyFactory::generateKey($password);

        $fileOne = new ReadOnlyFile($filename, $key);

        $fp      = \fopen($filename, 'rb');
        $fileTwo = new ReadOnlyFile($fp, $key);

        self::assertSame(
            $fileOne->getHash(),
            $fileTwo->getHash()
        );

        \fclose($fp);
    }

    public function testRead()
    {
        $filename = vfsStream::newFile('temp.txt')->at($this->root)->url();

        $buf = \random_bytes(65537);

        \file_put_contents($filename, $buf);

        $fStream = new ReadOnlyFile($filename);

        self::assertSame(65537, $fStream->getSize());
        self::assertSame($fStream->read(65537), $buf);

        $fStream->seek(0);
    }

    /**
     * Test for Time-of-check Time-of-use (TOCTOU) attacks (race conditions).
     *
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileModifiedException
     * @expectedExceptionMessage Read-only file has been modified since it was opened for reading.
     */
    public function testReadToThrowException()
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
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileAccessDeniedException
     * @expectedExceptionMessage This is a read-only file handle.
     */
    public function testWrite()
    {
        $filename = vfsStream::newFile('temp.txt')->at($this->root)->url();

        $fStream = new ReadOnlyFile($filename);

        $fStream->write('');
    }
}
