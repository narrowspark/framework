<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use Nyholm\NSA;
use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class StreamTest extends TestCase
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

        $this->pipeFh = \popen('php StreamTest.php &', 'r');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        if (\is_resource($this->pipeFh)) {
            \stream_get_contents($this->pipeFh); // prevent broken pipe error message
            \pclose($this->pipeFh);
        }
    }

    public function testConstructorThrowsExceptionOnInvalidArgument(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\UnexpectedValueException::class);

        new Stream(true);
    }

    public function testConstructorInitializesProperties(): void
    {
        $handle = \fopen('php://temp', 'r+b');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        static::assertTrue($stream->isReadable());
        static::assertTrue($stream->isWritable());
        static::assertTrue($stream->isSeekable());
        static::assertEquals('php://temp', $stream->getMetadata('uri'));
        static::assertInternalType('array', $stream->getMetadata());
        static::assertEquals(4, $stream->getSize());
        static::assertFalse($stream->eof());

        $stream->close();
    }

    public function testStreamClosesHandleOnDestruct(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);

        unset($stream);

        static::assertSame('Unknown', \get_resource_type($handle));
    }

    public function testConvertsToString(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        static::assertEquals('data', (string) $stream);
        static::assertEquals('data', (string) $stream);

        $stream->close();
    }

    public function testGetsContents(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        static::assertEquals('', $stream->getContents());

        $stream->seek(0);

        static::assertEquals('data', $stream->getContents());
        static::assertEquals('', $stream->getContents());

        $stream->close();
    }

    public function testChecksEof(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        static::assertSame(4, $stream->tell(), 'Stream cursor already at the end');
        static::assertFalse($stream->eof(), 'Stream still not eof');
        static::assertSame('', $stream->read(1), 'Need to read one more byte to reach eof');
        static::assertTrue($stream->eof());

        $stream->close();
    }

    public function testGetSize(): void
    {
        $size   = \filesize(__FILE__);
        $handle = \fopen(__FILE__, 'rb');

        $stream = new Stream($handle);

        static::assertEquals($size, $stream->getSize());
        // Load from cache
        static::assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function testEnsuresSizeIsConsistent(): void
    {
        $h = \fopen('php://temp', 'w+b');
        static::assertEquals(3, \fwrite($h, 'foo'));

        $stream = new Stream($h);

        static::assertEquals(3, $stream->getSize());
        static::assertEquals(4, $stream->write('test'));
        static::assertEquals(7, $stream->getSize());
        static::assertEquals(7, $stream->getSize());

        $stream->close();
    }

    public function testProvidesStreamPosition(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);

        static::assertEquals(0, $stream->tell());

        $stream->write('foo');

        static::assertEquals(3, $stream->tell());

        $stream->seek(1);

        static::assertEquals(1, $stream->tell());
        static::assertSame(\ftell($handle), $stream->tell());

        $stream->close();
    }

    public function testDetachStreamAndClearProperties(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);

        static::assertSame($handle, $stream->detach());
        static::assertInternalType('resource', $handle, 'Stream is not closed');
        static::assertNull($stream->detach());
        self::assertStreamStateAfterClosedOrDetached($stream);

        $stream->close();
    }

    public function testCloseResourceAndClearProperties(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        $stream->close();

        static::assertSame('Unknown', \get_resource_type($handle));
        self::assertStreamStateAfterClosedOrDetached($stream);
    }

    public function testDoesNotThrowInToString(): void
    {
        $body   = 'foo';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $stream = new NoSeekStream($stream);

        static::assertEquals('foo', (string) $stream);
    }

    public function testStreamReadingWithZeroLength(): void
    {
        $r      = \fopen('php://temp', 'rb');
        $stream = new Stream($r);

        static::assertSame('', $stream->read(0));

        $stream->close();
    }

    public function testStreamReadingWithNegativeLength(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Length parameter cannot be negative');

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

    public function testStreamReadingFreadError(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Unable to read from stream');

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

        $stream->expects(static::any())
            ->method('isSeekable')
            ->will(static::returnValue(false));

        static::assertEquals('FOO BAR', $stream->__toString());
    }

    /**
     * @dataProvider dataProviderForReadableStreams
     *
     * @param string $mode
     * @param string $func
     * @param bool   $createFile
     */
    public function testForReadableStreams(string $mode, string $func, $createFile = false): void
    {
        $tmpnam = self::normalizeDirectorySeparator(\sys_get_temp_dir() . '/' . ((string) \random_int(100, 999)) . $mode . $func);

        if ($createFile) {
            \touch($tmpnam);
        }

        $stream = new Stream($func($tmpnam, $mode));

        static::assertTrue($stream->isReadable());

        @\unlink($tmpnam);
    }

    public function dataProviderForReadableStreams(): array
    {
        return [
            ['r', 'fopen', true],
            ['w+', 'fopen'],
            ['w+b', 'fopen'],
            ['w+b', 'fopen'],
            ['w+t', 'fopen'],
            ['r+', 'fopen', true],
            ['c+', 'fopen'],
            ['cb+', 'fopen'],
            ['c+b', 'fopen'],
            ['c+t', 'fopen'],
            ['rb', 'fopen', true],
            ['r+b', 'fopen', true],
            ['x+', 'fopen'],
            ['x+b', 'fopen'],
            ['xb+', 'fopen'],
            ['x+t', 'fopen'],
            ['rt', 'fopen', true],
            ['r+t', 'fopen', true],
            ['a+', 'fopen'],
            ['a+b', 'fopen'],
            ['ab+', 'fopen'],
            ['a+t', 'fopen'],
            ['r+b', 'fopen', true],
            ['r1', 'gzopen', true],
            ['r2', 'gzopen', true],
            ['r3', 'gzopen', true],
            ['r4', 'gzopen', true],
            ['r5', 'gzopen', true],
            ['r6', 'gzopen', true],
            ['r7', 'gzopen', true],
            ['r8', 'gzopen', true],
            ['r9', 'gzopen', true],
            ['rb1', 'gzopen', true],
            ['rb2', 'gzopen', true],
            ['rb3', 'gzopen', true],
            ['rb4', 'gzopen', true],
            ['rb5', 'gzopen', true],
            ['rb6', 'gzopen', true],
            ['rb7', 'gzopen', true],
            ['rb8', 'gzopen', true],
            ['rb9', 'gzopen', true],
            ['rb1f', 'gzopen', true],
            ['rb2f', 'gzopen', true],
            ['rb3f', 'gzopen', true],
            ['rb4f', 'gzopen', true],
            ['rb5f', 'gzopen', true],
            ['rb6f', 'gzopen', true],
            ['rb7f', 'gzopen', true],
            ['rb8f', 'gzopen', true],
            ['rb9f', 'gzopen', true],
            ['rb1h', 'gzopen', true],
            ['rb2h', 'gzopen', true],
            ['rb3h', 'gzopen', true],
            ['rb4h', 'gzopen', true],
            ['rb5h', 'gzopen', true],
            ['rb6h', 'gzopen', true],
            ['rb7h', 'gzopen', true],
            ['rb8h', 'gzopen', true],
            ['rb9h', 'gzopen', true],
            ['rb1R', 'gzopen', true],
            ['rb2R', 'gzopen', true],
            ['rb3R', 'gzopen', true],
            ['rb4R', 'gzopen', true],
            ['rb5R', 'gzopen', true],
            ['rb6R', 'gzopen', true],
            ['rb7R', 'gzopen', true],
            ['rb8R', 'gzopen', true],
            ['rb9R', 'gzopen', true],
            ['rb1F', 'gzopen', true],
            ['rb2F', 'gzopen', true],
            ['rb3F', 'gzopen', true],
            ['rb4F', 'gzopen', true],
            ['rb5F', 'gzopen', true],
            ['rb6F', 'gzopen', true],
            ['rb7F', 'gzopen', true],
            ['rb8F', 'gzopen', true],
            ['rb9F', 'gzopen', true],
        ];
    }

    /**
     * @dataProvider dataProviderForWritableStreams
     *
     * @param string $mode
     * @param string $func
     * @param bool   $createFile
     */
    public function testForWritableStreams(string $mode, string $func, $createFile = false): void
    {
        $tmpnam = self::normalizeDirectorySeparator(\sys_get_temp_dir() . '/' . ((string) \random_int(100, 999)) . $mode . $func);

        if ($createFile) {
            \touch($tmpnam);
        }

        $stream = new Stream($func($tmpnam, $mode));

        static::assertTrue($stream->isWritable());

        if ($createFile && \file_exists($tmpnam)) {
            @\unlink($tmpnam);
        }
    }

    public function dataProviderForWritableStreams(): array
    {
        return [
            ['w', 'fopen'],
            ['w+', 'fopen'],
            ['rw', 'fopen', true],
            ['r+', 'fopen', true],
            ['x', 'fopen'],
            ['x+', 'fopen'],
            ['c', 'fopen'],
            ['c+', 'fopen'],
            ['wb', 'fopen'],
            ['w+b', 'fopen'],
            ['r+b', 'fopen', true],
            ['x+b', 'fopen'],
            ['c+b', 'fopen'],
            ['w+t', 'fopen'],
            ['r+t', 'fopen', true],
            ['x+t', 'fopen'],
            ['c+', 'fopen'],
            ['a', 'fopen'],
            ['a+', 'fopen'],
            ['a+b', 'fopen'],
            ['ab', 'fopen'],
            ['ab+', 'fopen'],
            ['w1', 'gzopen'],
            ['w2', 'gzopen'],
            ['w3', 'gzopen'],
            ['w4', 'gzopen'],
            ['w5', 'gzopen'],
            ['w6', 'gzopen'],
            ['w7', 'gzopen'],
            ['w8', 'gzopen'],
            ['w9', 'gzopen'],
            ['wb1', 'gzopen'],
            ['wb2', 'gzopen'],
            ['wb3', 'gzopen'],
            ['wb4', 'gzopen'],
            ['wb5', 'gzopen'],
            ['wb6', 'gzopen'],
            ['wb7', 'gzopen'],
            ['wb8', 'gzopen'],
            ['wb9', 'gzopen'],
            ['wb1f', 'gzopen'],
            ['wb2f', 'gzopen'],
            ['wb3f', 'gzopen'],
            ['wb4f', 'gzopen'],
            ['wb5f', 'gzopen'],
            ['wb6f', 'gzopen'],
            ['wb7f', 'gzopen'],
            ['wb8f', 'gzopen'],
            ['wb9f', 'gzopen'],
            ['wb1h', 'gzopen'],
            ['wb2h', 'gzopen'],
            ['wb3h', 'gzopen'],
            ['wb4h', 'gzopen'],
            ['wb5h', 'gzopen'],
            ['wb6h', 'gzopen'],
            ['wb7h', 'gzopen'],
            ['wb8h', 'gzopen'],
            ['wb9h', 'gzopen'],
            ['wb1R', 'gzopen'],
            ['wb2R', 'gzopen'],
            ['wb3R', 'gzopen'],
            ['wb4R', 'gzopen'],
            ['wb5R', 'gzopen'],
            ['wb6R', 'gzopen'],
            ['wb7R', 'gzopen'],
            ['wb8R', 'gzopen'],
            ['wb9R', 'gzopen'],
            ['wb1F', 'gzopen'],
            ['wb2F', 'gzopen'],
            ['wb3F', 'gzopen'],
            ['wb4F', 'gzopen'],
            ['wb5F', 'gzopen'],
            ['wb6F', 'gzopen'],
            ['wb7F', 'gzopen'],
            ['wb8F', 'gzopen'],
            ['wb9F', 'gzopen'],
        ];
    }

    public function testIsPipe(): void
    {
        $stream = new Stream($this->pipeFh);

        static::assertTrue(NSA::invokeMethod($stream, 'isPipe'));

        $stream->detach();

        static::assertFalse(NSA::invokeMethod($stream, 'isPipe'));

        $fileStream = new Stream(\fopen(__FILE__, 'r'));

        static::assertFalse(NSA::invokeMethod($fileStream, 'isPipe'));
    }

    public function testIsPipeReadable(): void
    {
        $stream = new Stream($this->pipeFh);

        static::assertTrue($stream->isReadable());
    }

    public function testPipeIsNotSeekable(): void
    {
        $stream = new Stream($this->pipeFh);

        static::assertFalse($stream->isSeekable());
    }

    public function testCannotSeekPipe(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');

        $stream = new Stream($this->pipeFh);

        $stream->seek(0);
    }

    public function testCannotTellPipe(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine stream position.');

        $stream = new Stream($this->pipeFh);

        $stream->tell();
    }

    public function testCannotRewindPipe(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');

        $stream = new Stream($this->pipeFh);

        $stream->rewind();
    }

    public function testPipeGetSizeYieldsNull(): void
    {
        $stream = new Stream($this->pipeFh);

        static::assertNull($stream->getSize());
    }

    public function testClosePipe(): void
    {
        $stream = new Stream($this->pipeFh);

        \stream_get_contents($this->pipeFh); // prevent broken pipe error message

        $stream->close();

        $this->pipeFh = null;

        static::assertFalse(NSA::invokeMethod($stream, 'isPipe'));
    }

    public function testPipeToString(): void
    {
        $stream = new Stream($this->pipeFh);

        static::assertSame("Could not open input file: StreamTest.php\n", $stream->__toString());
    }

    public function testPipeGetContents(): void
    {
        $stream = new Stream($this->pipeFh);

        $contents = \trim($stream->getContents());

        static::assertSame('Could not open input file: StreamTest.php', $contents);
    }

    private static function assertStreamStateAfterClosedOrDetached(Stream $stream): void
    {
        static::assertFalse($stream->isReadable());
        static::assertFalse($stream->isWritable());
        static::assertFalse($stream->isSeekable());
        static::assertNull($stream->getSize());
        static::assertSame([], $stream->getMetadata());
        static::assertNull($stream->getMetadata('foo'));

        $throws = function (callable $fn): void {
            try {
                $fn();
            } catch (Throwable $e) {
                static::assertContains('Stream is detached', $e->getMessage());

                return;
            }

            static::fail('Exception should be thrown after the stream is detached.');
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

        \set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if ($errno === \E_USER_ERROR) {
                static::assertContains('::__toString exception: ', $errstr);

                return '';
            }
        });

        static::assertSame('', (string) $stream);

        \restore_error_handler();
    }
}
namespace Viserio\Component\Http;

use Viserio\Component\Http\Tests\StreamTest;

function fread($handle, $length)
{
    return StreamTest::$isFreadError ? false : \fread($handle, $length);
}
