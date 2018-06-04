<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Http\Exception\LogicException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;

/**
 * @internal
 */
final class FnStreamTest extends TestCase
{
    public function testDoNotAllowUnserialization(): void
    {
        $this->expectException(LogicException::class);

        $a = new FnStream([]);
        $b = \serialize($a);

        \unserialize($b);
    }

    public function testThrowsWhenNotImplemented(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('seek() is not implemented in the FnStream');

        (new FnStream([]))->seek(1);
    }

    public function testProxiesToFunction(): void
    {
        $stream = new FnStream([
            'read' => function ($len) {
                $this->assertEquals(3, $len);

                return 'foo';
            },
        ]);

        $this->assertEquals('foo', $stream->read(3));
    }

    public function testCanCloseOnDestruct(): void
    {
        $called = false;

        $stream = new FnStream([
            'close' => function () use (&$called): void {
                $called = true;
            },
        ]);
        unset($stream);

        $this->assertTrue($called);
    }

    public function doesNotRequireClose(): void
    {
        $stream = new FnStream([]);
        unset($stream);
    }

    public function testDecoratesStream(): void
    {
        $body   = 'foo';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);
        $stream1 = new Stream($stream);
        $stream2 = FnStream::decorate($stream1, []);

        $this->assertEquals(3, $stream2->getSize());
        $this->assertEquals($stream2->isWritable(), true);
        $this->assertEquals($stream2->isReadable(), true);
        $this->assertEquals($stream2->isSeekable(), true);
        $this->assertEquals($stream2->read(3), 'foo');
        $this->assertEquals($stream2->tell(), 3);
        $this->assertEquals($stream1->tell(), 3);
        $this->assertSame('', $stream1->read(1));
        $this->assertEquals($stream2->eof(), true);
        $this->assertEquals($stream1->eof(), true);
        $stream2->seek(0);
        $this->assertEquals('foo', (string) $stream2);
        $stream2->seek(0);
        $this->assertEquals('foo', $stream2->getContents());
        $this->assertEquals($stream1->getMetadata(), $stream2->getMetadata());
        $stream2->seek(0, \SEEK_END);
        $stream2->write('bar');
        $this->assertEquals('foobar', (string) $stream2);
        $this->assertInternalType('resource', $stream2->detach());
        $stream2->close();
    }

    public function testDecoratesWithCustomizations(): void
    {
        $called = false;

        $body   = 'foo';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream1 = new Stream($stream);
        $stream2 = FnStream::decorate($stream1, [
            'read' => function ($len) use (&$called, $stream1) {
                $called = true;

                return $stream1->read($len);
            },
        ]);

        $this->assertEquals('foo', $stream2->read(3));
        $this->assertTrue($called);
    }
}
