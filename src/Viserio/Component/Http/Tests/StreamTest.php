<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Nyholm\NSA;
use Throwable;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;
use Viserio\Contract\Http\Exception\RuntimeException;
use Viserio\Contract\Http\Exception\UnexpectedValueException;

/**
 * @internal
 *
 * @small
 */
final class StreamTest extends MockeryTestCase
{
    /** @var bool */
    public static $isFreadError = false;

    /** @var string */
    private $tmpnam;

    /** @var resource pipe stream file handle */
    private $pipeFh;

    /** @var string */
    private $tmpPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var resource $resource */
        $resource = \popen('php StreamTest.php &', 'r');

        $this->pipeFh = $resource;
        $this->tmpPath = __DIR__ . \DIRECTORY_SEPARATOR . 'tmp';

        @\mkdir($this->tmpPath);
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

        \array_map(static function ($value): void {
            @\unlink($value);
        }, \glob($this->tmpPath . \DIRECTORY_SEPARATOR . '*'));
        @\rmdir($this->tmpPath);
    }

    public function testConstructorThrowsExceptionOnInvalidArgument(): void
    {
        $this->expectException(UnexpectedValueException::class);

        new Stream(true);
    }

    public function testConstructorInitializesProperties(): void
    {
        $handle = \fopen('php://temp', 'r+b');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isSeekable());
        self::assertEquals('php://temp', $stream->getMetadata('uri'));
        self::assertIsArray($stream->getMetadata());
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
        $handle = \fopen('php://temp', 'w+b');
        \fwrite($handle, 'data');

        $stream = new Stream($handle);

        self::assertEquals('data', (string) $stream);
        self::assertEquals('data', (string) $stream);

        $stream->close();
    }

    public function testGetsContents(): void
    {
        $handle = \fopen('php://temp', 'w+b');
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
        $handle = \fopen('php://temp', 'w+b');
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
        $size = \filesize(__FILE__);
        $handle = \fopen(__FILE__, 'rb');

        $stream = new Stream($handle);

        self::assertEquals($size, $stream->getSize());
        // Load from cache
        self::assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function testEnsuresSizeIsConsistent(): void
    {
        $h = \fopen('php://temp', 'w+b');
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
        $handle = \fopen('php://temp', 'w+b');
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
        self::assertIsResource($handle, 'Stream is not closed');
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
        $body = 'foo';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $stream = new NoSeekStream($stream);

        self::assertEquals('foo', (string) $stream);
    }

    public function testStreamReadingWithZeroLength(): void
    {
        $r = \fopen('php://temp', 'rb');
        $stream = new Stream($r);

        self::assertSame('', $stream->read(0));

        $stream->close();
    }

    public function testStreamReadingWithNegativeLength(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Length parameter cannot be negative');

        $r = \fopen('php://temp', 'rb');
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to read from stream');

        self::$isFreadError = true;

        $r = \fopen('php://temp', 'rb');
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
        $this->tmpnam = \tempnam($this->tmpPath, 'diac');

        \file_put_contents($this->tmpnam, 'FOO BAR');

        $resource = \fopen($this->tmpnam, 'rb');

        $stream = \Mockery::mock(new Stream($resource));
        $stream->shouldReceive('isSeekable')
            ->andReturn(false);

        self::assertEquals('FOO BAR', $stream->__toString());
    }

    /**
     * @dataProvider provideForReadableStreamsCases
     *
     * @param string $mode
     * @param string $func
     * @param bool   $createFile
     */
    public function testForReadableStreams(string $mode, string $func, $createFile = false): void
    {
        $tmpnam = $this->tmpPath . \DIRECTORY_SEPARATOR . ((string) \random_int(100, 999)) . $mode . $func;

        if ($createFile) {
            \touch($tmpnam);
        }

        $stream = new Stream($func($tmpnam, $mode));

        self::assertTrue($stream->isReadable());

        @\unlink($tmpnam);
    }

    public function provideForReadableStreamsCases(): iterable
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
     * @dataProvider provideForWritableStreamsCases
     *
     * @param string $mode
     * @param string $func
     * @param bool   $createFile
     */
    public function testForWritableStreams(string $mode, string $func, $createFile = false): void
    {
        $tmpnam = $this->tmpPath . \DIRECTORY_SEPARATOR . ((string) \random_int(100, 999)) . $mode . $func;

        if ($createFile) {
            \touch($tmpnam);
        }

        $stream = new Stream($func($tmpnam, $mode));

        self::assertTrue($stream->isWritable());

        if ($createFile && \file_exists($tmpnam)) {
            @\unlink($tmpnam);
        }
    }

    public function provideForWritableStreamsCases(): iterable
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

        self::assertTrue(NSA::invokeMethod($stream, 'isPipe'));

        $stream->detach();

        self::assertFalse(NSA::invokeMethod($stream, 'isPipe'));

        $fileStream = new Stream(\fopen(__FILE__, 'r'));

        self::assertFalse(NSA::invokeMethod($fileStream, 'isPipe'));
    }

    public function testIsPipeReadable(): void
    {
        $stream = new Stream($this->pipeFh);

        self::assertTrue($stream->isReadable());
    }

    public function testPipeIsNotSeekable(): void
    {
        $stream = new Stream($this->pipeFh);

        self::assertFalse($stream->isSeekable());
    }

    public function testCannotSeekPipe(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');

        $stream = new Stream($this->pipeFh);

        $stream->seek(0);
    }

    public function testCannotTellPipe(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine stream position.');

        $stream = new Stream($this->pipeFh);

        $stream->tell();
    }

    public function testCannotRewindPipe(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');

        $stream = new Stream($this->pipeFh);

        $stream->rewind();
    }

    public function testPipeGetSizeYieldsNull(): void
    {
        $stream = new Stream($this->pipeFh);

        self::assertNull($stream->getSize());
    }

    public function testClosePipe(): void
    {
        $stream = new Stream($this->pipeFh);

        \stream_get_contents($this->pipeFh); // prevent broken pipe error message

        $stream->close();

        $this->pipeFh = null;

        self::assertFalse(NSA::invokeMethod($stream, 'isPipe'));
    }

    public function testPipeToString(): void
    {
        $stream = new Stream($this->pipeFh);

        self::assertSame("Could not open input file: StreamTest.php\n", $stream->__toString());
    }

    public function testPipeGetContents(): void
    {
        $stream = new Stream($this->pipeFh);

        $contents = \trim($stream->getContents());

        self::assertSame('Could not open input file: StreamTest.php', $contents);
    }

    /**
     * @param Stream $stream
     */
    private static function assertStreamStateAfterClosedOrDetached(Stream $stream): void
    {
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertFalse($stream->isSeekable());
        self::assertNull($stream->getSize());
        self::assertSame([], $stream->getMetadata());
        self::assertNull($stream->getMetadata('foo'));

        $throws = static function (callable $fn): void {
            try {
                $fn();
            } catch (Throwable $e) {
                static::assertStringContainsString('Stream is detached', $e->getMessage());

                return;
            }

            static::fail('Exception should be thrown after the stream is detached.');
        };
        $throws(static function () use ($stream): void {
            $stream->read(10);
        });
        $throws(static function () use ($stream): void {
            $stream->write('bar');
        });
        $throws(static function () use ($stream): void {
            $stream->seek(10);
        });
        $throws(static function () use ($stream): void {
            $stream->tell();
        });
        $throws(static function () use ($stream): void {
            $stream->eof();
        });
        $throws(static function () use ($stream): void {
            $stream->getContents();
        });

        \set_error_handler(static function ($errno, $errstr, $errfile, $errline) {
            if ($errno === \E_USER_ERROR) {
                static::assertStringContainsString('::__toString exception: ', $errstr);

                return '';
            }
        });

        self::assertSame('', (string) $stream);

        \restore_error_handler();
    }
}

namespace Viserio\Component\Http;

use Viserio\Component\Http\Tests\StreamTest;

function fread($handle, $length)
{
    return StreamTest::$isFreadError ? false : \fread($handle, $length);
}
