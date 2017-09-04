<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Filesystem\Stream\ReadOnlyFile;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class ReadOnlyFileTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testGetHashWithEmptyKey()
    {
        $filename = \tempnam('/tmp', 'x');

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
        $filename = \tempnam('/tmp', 'x');

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
        $filename = \tempnam('/tmp', 'x');

        $buf = \random_bytes(65537);

        \file_put_contents($filename, $buf);

        $fStream = new ReadOnlyFile($filename);

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
        $filename = \tempnam('/tmp', 'x');

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
}
