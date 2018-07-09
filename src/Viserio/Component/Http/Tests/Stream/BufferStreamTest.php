<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\BufferStream;

/**
 * @internal
 */
final class BufferStreamTest extends TestCase
{
    public function testHasMetadata(): void
    {
        $buffer = new BufferStream(10);

        static::assertTrue($buffer->isReadable());
        static::assertTrue($buffer->isWritable());
        static::assertFalse($buffer->isSeekable());
        static::assertNull($buffer->getMetadata('foo'));
        static::assertEquals(10, $buffer->getMetadata('hwm'));
        static::assertEquals([], $buffer->getMetadata());
    }

    public function testRemovesReadDataFromBuffer(): void
    {
        $buffer = new BufferStream();

        static::assertEquals(3, $buffer->write('foo'));
        static::assertEquals(3, $buffer->getSize());
        static::assertFalse($buffer->eof());
        static::assertEquals('foo', $buffer->read(10));
        static::assertTrue($buffer->eof());
        static::assertEquals('', $buffer->read(10));
    }

    public function testCanCastToStringOrGetContents(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot determine the position of a BufferStream');

        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->write('baz');

        static::assertEquals('foo', $buffer->read(3));

        $buffer->write('bar');

        static::assertEquals('bazbar', (string) $buffer);
        $buffer->tell();
    }

    public function testDetachClearsBuffer(): void
    {
        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->detach();

        static::assertTrue($buffer->eof());
        static::assertEquals(3, $buffer->write('abc'));
        static::assertEquals('abc', $buffer->read(10));
    }

    public function testExceedingHighwaterMarkReturnsFalseButStillBuffers(): void
    {
        $buffer = new BufferStream(5);

        static::assertEquals(3, $buffer->write('hi '));
        static::assertSame(0, $buffer->write('hello'));
        static::assertEquals('hi hello', (string) $buffer);
        static::assertEquals(4, $buffer->write('test'));
    }
}
