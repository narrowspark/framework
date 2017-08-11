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
    private $tmpnam;

    /**
     * @expectedException \Viserio\Component\Contracts\Http\Exception\UnexpectedValueException
     */
    public function testConstructorThrowsExceptionOnInvalidArgument(): void
    {
        new Stream(true);
    }

    public function testConstructorInitializesProperties(): void
    {
        $handle = \fopen('php://temp', 'rb+');
        \fwrite($handle, 'data');

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

    public function testStreamClosesHandleOnDestruct(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        unset($stream);
        self::assertFalse(\is_resource($handle));
    }

    public function testConvertsToString(): void
    {
        $handle = \fopen('php://temp', 'wb+');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);
        self::assertEquals('data', (string) $stream);
        self::assertEquals('data', (string) $stream);
        $stream->close();
    }

    public function testGetsContents(): void
    {
        $handle = \fopen('php://temp', 'wb+');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        self::assertEquals('', $stream->getContents());

        $stream->seek(0);

        self::assertEquals('data', $stream->getContents());
        self::assertEquals('', $stream->getContents());

        $stream->close();
    }

    public function testChecksEof(): void
    {
        $handle = \fopen('php://temp', 'wb+');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        self::assertSame(4, $stream->tell(), 'Stream cursor already at the end');
        self::assertFalse($stream->eof(), 'Stream still not eof');
        self::assertSame('', $stream->read(1), 'Need to read one more byte to reach eof');
        self::assertTrue($stream->eof());

        $stream->close();
    }

    public function testGetSize(): void
    {
        $size   = \filesize(__FILE__);
        $handle = \fopen(__FILE__, 'rb');

        $stream = new Stream($handle);
        self::assertEquals($size, $stream->getSize());
        // Load from cache
        self::assertEquals($size, $stream->getSize());
        $stream->close();
    }

    public function testEnsuresSizeIsConsistent(): void
    {
        $h = \fopen('php://temp', 'wb+');
        self::assertEquals(3, \fwrite($h, 'foo'));

        $stream = new Stream($h);
        self::assertEquals(3, $stream->getSize());
        self::assertEquals(4, $stream->write('test'));
        self::assertEquals(7, $stream->getSize());
        self::assertEquals(7, $stream->getSize());
        $stream->close();
    }

    public function testProvidesStreamPosition(): void
    {
        $handle = \fopen('php://temp', 'wb+');
        $stream = new Stream($handle);

        self::assertEquals(0, $stream->tell());

        $stream->write('foo');
        self::assertEquals(3, $stream->tell());

        $stream->seek(1);
        self::assertEquals(1, $stream->tell());
        self::assertSame(\ftell($handle), $stream->tell());

        $stream->close();
    }

    public function testDetachStreamAndClearProperties(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);

        self::assertSame($handle, $stream->detach());
        self::assertTrue(\is_resource($handle), 'Stream is not closed');
        self::assertNull($stream->detach());
        self::assertStreamStateAfterClosedOrDetached($stream);

        $stream->close();
    }

    public function testCloseResourceAndClearProperties(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        $stream->close();

        self::assertFalse(\is_resource($handle));
        self::assertStreamStateAfterClosedOrDetached($stream);
    }

    public function testDoesNotThrowInToString(): void
    {
        $body   = 'foo';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $stream = new NoSeekStream($stream);

        self::assertEquals('foo', (string) $stream);
    }

    public function testStreamReadingWithZeroLength(): void
    {
        $r      = \fopen('php://temp', 'rb');
        $stream = new Stream($r);

        self::assertSame('', $stream->read(0));

        $stream->close();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Length parameter cannot be negative
     */
    public function testStreamReadingWithNegativeLength(): void
    {
        $r      = \fopen('php://temp', 'rb');
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
    public function testStreamReadingFreadError(): void
    {
        self::$isFreadError = true;

        $r      = \fopen('php://temp', 'rb');
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

    public function testCanReadContentFromNotSeekableResource(): void
    {
        $this->tmpnam = \tempnam(\sys_get_temp_dir(), 'diac');

        \file_put_contents($this->tmpnam, 'FOO BAR');

        $resource = \fopen($this->tmpnam, 'rb');
        $stream   = $this->getMockBuilder(Stream::class)
            ->setConstructorArgs([$resource])
            ->setMethods(['isSeekable'])
            ->getMock();

        $stream->expects($this->any())
            ->method('isSeekable')
            ->will($this->returnValue(false));

        self::assertEquals('FOO BAR', $stream->__toString());
    }

    private static function assertStreamStateAfterClosedOrDetached(Stream $stream): void
    {
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertFalse($stream->isSeekable());
        self::assertNull($stream->getSize());
        self::assertSame([], $stream->getMetadata());
        self::assertNull($stream->getMetadata('foo'));

        $throws = function (callable $fn): void {
            try {
                $fn();
            } catch (Throwable $e) {
                self::assertContains('Stream is detached', $e->getMessage());

                return;
            }

            self::fail('Exception should be thrown after the stream is detached.');
        };
        $throws(function () use ($stream): void {
            $stream->read(10);
        });
        $throws(function () use ($stream): void {
            $stream->write('bar');
        });
        $throws(function () use ($stream): void {
            $stream->seek(10);
        });
        $throws(function () use ($stream): void {
            $stream->tell();
        });
        $throws(function () use ($stream): void {
            $stream->eof();
        });
        $throws(function () use ($stream): void {
            $stream->getContents();
        });

        self::assertSame('', (string) $stream);
    }
}
namespace Viserio\Component\Http;

use Viserio\Component\Http\Tests\StreamTest;

function fread($handle, $length)
{
    return StreamTest::$isFreadError ? false : \fread($handle, $length);
}
