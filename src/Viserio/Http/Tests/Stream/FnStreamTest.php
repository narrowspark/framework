<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Stream;
use Viserio\Http\Stream\FnStream;
use PHPUnit\Framework\TestCase;

class FnStreamTest extends TestCase
{
    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage seek() is not implemented in the FnStream
     */
    public function testThrowsWhenNotImplemented()
    {
        (new FnStream([]))->seek(1);
    }

    public function testProxiesToFunction()
    {
        $stream = new FnStream([
            'read' => function ($len) {
                self::assertEquals(3, $len);

                return 'foo';
            },
        ]);

        self::assertEquals('foo', $stream->read(3));
    }

    public function testCanCloseOnDestruct()
    {
        $called = false;

        $stream = new FnStream([
            'close' => function () use (&$called) {
                $called = true;
            },
        ]);
        unset($stream);

        self::assertTrue($called);
    }

    public function doesNotRequireClose()
    {
        $stream = new FnStream([]);
        unset($stream);
    }

    public function testDecoratesStream()
    {
        $body   = 'foo';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);
        $stream1 = new Stream($stream);
        $stream2 = FnStream::decorate($stream1, []);

        self::assertEquals(3, $stream2->getSize());
        self::assertEquals($stream2->isWritable(), true);
        self::assertEquals($stream2->isReadable(), true);
        self::assertEquals($stream2->isSeekable(), true);
        self::assertEquals($stream2->read(3), 'foo');
        self::assertEquals($stream2->tell(), 3);
        self::assertEquals($stream1->tell(), 3);
        self::assertSame('', $stream1->read(1));
        self::assertEquals($stream2->eof(), true);
        self::assertEquals($stream1->eof(), true);
        $stream2->seek(0);
        self::assertEquals('foo', (string) $stream2);
        $stream2->seek(0);
        self::assertEquals('foo', $stream2->getContents());
        self::assertEquals($stream1->getMetadata(), $stream2->getMetadata());
        $stream2->seek(0, SEEK_END);
        $stream2->write('bar');
        self::assertEquals('foobar', (string) $stream2);
        self::assertInternalType('resource', $stream2->detach());
        $stream2->close();
    }

    public function testDecoratesWithCustomizations()
    {
        $called = false;

        $body   = 'foo';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream1 = new Stream($stream);
        $stream2 = FnStream::decorate($stream1, [
            'read' => function ($len) use (&$called, $stream1) {
                $called = true;

                return $stream1->read($len);
            },
        ]);

        self::assertEquals('foo', $stream2->read(3));
        self::assertTrue($called);
    }
}
