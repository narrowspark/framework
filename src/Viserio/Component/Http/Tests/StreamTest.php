<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use Nyholm\NSA;
use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class StreamTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var bool
     */
    public static $isFreadError = false;

    /**
     * @var string
     */
    private $tmpnam;

    /**
     * @var resource pipe stream file handle
     */
    private $pipeFh;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pipeFh = \popen('echo 12', 'r');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (\is_resource($this->pipeFh)) {
            \stream_get_contents($this->pipeFh); // prevent broken pipe error message
        }
    }

    /**
     * @expectedException \Viserio\Component\Contract\Http\Exception\UnexpectedValueException
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

        self::assertSame('Unknown', \get_resource_type($handle));
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
        self::assertInternalType('resource', $handle, 'Stream is not closed');
        self::assertNull($stream->detach());
        self::assertStreamStateAfterClosedOrDetached($stream);

        $stream->close();
    }

    public function testCloseResourceAndClearProperties(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        $stream->close();

        self::assertSame('Unknown', \get_resource_type($handle));
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

    /**
     * @dataProvider dataProviderForReadableStreams
     *
     * @param string $mode
     * @param string $func
     * @param bool   $createFile
     */
    public function testForReadableStreams(string $mode, string $func, $createFile = false)
    {
        $tmpnam = self::normalizeDirectorySeparator(\sys_get_temp_dir() . '/' . ((string) \random_int(100, 999)) . $mode . $func);

        if ($createFile) {
            \touch($tmpnam);
        }

        $resource = $func($tmpnam, $mode);

        $stream = new Stream($resource);

        self::assertTrue($stream->isReadable());

        @\unlink($tmpnam);
    }

    public function dataProviderForReadableStreams(): array
    {
        return [
            ['r', 'fopen', true],
            ['w+', 'fopen'],
            ['r+', 'fopen', true],
            ['x+', 'fopen'],
            ['c+', 'fopen'],
            ['rb', 'fopen', true],
            ['w+b', 'fopen'],
            ['r+b', 'fopen', true],
            ['x+b', 'fopen'],
            ['c+b', 'fopen'],
            ['rt', 'fopen', true],
            ['w+t', 'fopen'],
            ['r+t', 'fopen', true],
            ['x+t', 'fopen'],
            ['c+t', 'fopen'],
            ['a+', 'fopen'],
            ['a+b', 'fopen'],
            ['a+t', 'fopen'],
            ['rb+', 'fopen', true],
            ['wb+', 'fopen'],
            ['ab+', 'fopen'],
        ];
    }

    public function testIsPipe()
    {
        $stream = new Stream($this->pipeFh);

        self::assertTrue(NSA::getProperty($stream, 'isPipe'));

        $stream->detach();

        self::assertFalse(NSA::getProperty($stream, 'isPipe'));

        $fileStream = new Stream(\fopen(__FILE__, 'r'));

        self::assertFalse(NSA::getProperty($fileStream, 'isPipe'));
    }

    public function testIsPipeReadable()
    {
        $stream = new Stream($this->pipeFh);

        self::assertTrue($stream->isReadable());
    }

    public function testPipeIsNotSeekable()
    {
        $stream = new Stream($this->pipeFh);

        self::assertFalse($stream->isSeekable());
    }

    /**
     * @expectedException \Viserio\Component\Contract\Http\Exception\RuntimeException
     * @expectedExceptionMessage Stream is not seekable.
     */
    public function testCannotSeekPipe()
    {
        $stream = new Stream($this->pipeFh);

        $stream->seek(0);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Http\Exception\RuntimeException
     * @expectedExceptionMessage Unable to determine stream position.
     */
    public function testCannotTellPipe()
    {
        $stream = new Stream($this->pipeFh);

        $stream->tell();
    }

    /**
     * @expectedException \Viserio\Component\Contract\Http\Exception\RuntimeException
     * @expectedExceptionMessage Stream is not seekable.
     */
    public function testCannotRewindPipe()
    {
        $stream = new Stream($this->pipeFh);

        $stream->rewind();
    }

    public function testPipeGetSizeYieldsNull()
    {
        $stream = new Stream($this->pipeFh);

        self::assertNull($stream->getSize());
    }

    public function testClosePipe()
    {
        $stream = new Stream($this->pipeFh);

        \stream_get_contents($this->pipeFh); // prevent broken pipe error message

        $stream->close();

        $this->pipeFh = null;

        self::assertFalse(NSA::getProperty($stream, 'isPipe'));
    }

    public function testPipeToString()
    {
        $stream = new Stream($this->pipeFh);

        self::assertSame("12\n", $stream->__toString());
    }

    public function testPipeGetContents()
    {
        $stream = new Stream($this->pipeFh);

        $contents = \trim($stream->getContents());

        self::assertSame('12', $contents);
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
