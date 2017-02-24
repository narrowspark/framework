<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;

class StreamTest extends TestCase
{
    public static $isFreadError = false;

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnInvalidArgument()
    {
        new Stream(true);
    }

    public function testConstructorInitializesProperties()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'data');

        $stream = new Stream($handle);
        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isSeekable());
        self::assertEquals('php://temp', $stream->getMetadata('uri'));
        self::assertInternalType('array', $stream->getMetadata());
        self::assertEquals(4, $stream->getSize());
        self::assertFalse($stream->eof());
        $stream->close();
    }

    public function testStreamClosesHandleOnDestruct()
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        unset($stream);
        self::assertFalse(is_resource($handle));
    }

    public function testConvertsToString()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = new Stream($handle);
        self::assertEquals('data', (string) $stream);
        self::assertEquals('data', (string) $stream);
        $stream->close();
    }

    public function testGetsContents()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = new Stream($handle);
        self::assertEquals('', $stream->getContents());
        $stream->seek(0);
        self::assertEquals('data', $stream->getContents());
        self::assertEquals('', $stream->getContents());
    }

    public function testChecksEof()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = new Stream($handle);
        self::assertFalse($stream->eof());
        $stream->read(4);
        self::assertTrue($stream->eof());
        $stream->close();
    }

    public function testGetSize()
    {
        $size   = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');

        $stream = new Stream($handle);
        self::assertEquals($size, $stream->getSize());
        // Load from cache
        self::assertEquals($size, $stream->getSize());
        $stream->close();
    }

    public function testEnsuresSizeIsConsistent()
    {
        $h = fopen('php://temp', 'w+');
        self::assertEquals(3, fwrite($h, 'foo'));

        $stream = new Stream($h);
        self::assertEquals(3, $stream->getSize());
        self::assertEquals(4, $stream->write('test'));
        self::assertEquals(7, $stream->getSize());
        self::assertEquals(7, $stream->getSize());
        $stream->close();
    }

    public function testProvidesStreamPosition()
    {
        $handle = fopen('php://temp', 'w+');
        $stream = new Stream($handle);

        self::assertEquals(0, $stream->tell());

        $stream->write('foo');
        self::assertEquals(3, $stream->tell());

        $stream->seek(1);
        self::assertEquals(1, $stream->tell());
        self::assertSame(ftell($handle), $stream->tell());

        $stream->close();
    }

    public function testCanDetachStream()
    {
        $r      = fopen('php://temp', 'w+');
        $stream = new Stream($r);

        $stream->write('foo');
        self::assertTrue($stream->isReadable());
        self::assertSame($r, $stream->detach());
        $stream->detach();
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertFalse($stream->isSeekable());

        $throws = function (callable $fn) use ($stream) {
            try {
                $fn($stream);
                $this->fail();
            } catch (Throwable $e) {
            }
        };

        $throws(function ($stream) {
            $stream->read(10);
        });
        $throws(function ($stream) {
            $stream->write('bar');
        });
        $throws(function ($stream) {
            $stream->seek(10);
        });
        $throws(function ($stream) {
            $stream->tell();
        });
        $throws(function ($stream) {
            $stream->eof();
        });
        $throws(function ($stream) {
            $stream->getSize();
        });
        $throws(function ($stream) {
            $stream->getContents();
        });
        self::assertSame('', (string) $stream);
        $stream->close();
    }

    public function testCloseClearProperties()
    {
        $handle = fopen('php://temp', 'r+');
        $stream = new Stream($handle);
        $stream->close();

        self::assertFalse($stream->isSeekable());
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertSame(0, $stream->getSize());
        self::assertEmpty($stream->getMetadata());
    }

    public function testDoesNotThrowInToString()
    {
        $body   = 'foo';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream = new Stream($stream);
        $stream = new NoSeekStream($stream);

        self::assertEquals('foo', (string) $stream);
    }

    public function testStreamReadingWithZeroLength()
    {
        $r      = fopen('php://temp', 'r');
        $stream = new Stream($r);

        self::assertSame('', $stream->read(0));

        $stream->close();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Length parameter cannot be negative
     */
    public function testStreamReadingWithNegativeLength()
    {
        $r      = fopen('php://temp', 'r');
        $stream = new Stream($r);

        try {
            $stream->read(-1);
        } catch (Throwable $e) {
            $stream->close();
            throw $e;
        }

        $stream->close();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to read from stream
     */
    public function testStreamReadingFreadError()
    {
        self::$isFreadError = true;

        $r      = fopen('php://temp', 'r');
        $stream = new Stream($r);

        try {
            $stream->read(1);
        } catch (Throwable $e) {
            self::$isFreadError = false;
            $stream->close();

            throw $e;
        }

        self::$isFreadError = false;

        $stream->close();
    }
}
namespace Viserio\Component\Http;

use Viserio\Component\Http\Tests\StreamTest;

function fread($handle, $length)
{
    return StreamTest::$isFreadError ? false : \fread($handle, $length);
}
