<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\BufferStream;

class BufferStreamTest extends TestCase
{
    public function testHasMetadata(): void
    {
        $buffer = new BufferStream(10);

        self::assertTrue($buffer->isReadable());
        self::assertTrue($buffer->isWritable());
        self::assertFalse($buffer->isSeekable());
        self::assertNull($buffer->getMetadata('foo'));
        self::assertEquals(10, $buffer->getMetadata('hwm'));
        self::assertEquals([], $buffer->getMetadata());
    }

    public function testRemovesReadDataFromBuffer(): void
    {
        $buffer = new BufferStream();

        self::assertEquals(3, $buffer->write('foo'));
        self::assertEquals(3, $buffer->getSize());
        self::assertFalse($buffer->eof());
        self::assertEquals('foo', $buffer->read(10));
        self::assertTrue($buffer->eof());
        self::assertEquals('', $buffer->read(10));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot determine the position of a BufferStream
     */
    public function testCanCastToStringOrGetContents(): void
    {
        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->write('baz');

        self::assertEquals('foo', $buffer->read(3));

        $buffer->write('bar');

        self::assertEquals('bazbar', (string) $buffer);
        $buffer->tell();
    }

    public function testDetachClearsBuffer(): void
    {
        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->detach();

        self::assertTrue($buffer->eof());
        self::assertEquals(3, $buffer->write('abc'));
        self::assertEquals('abc', $buffer->read(10));
    }

    public function testExceedingHighwaterMarkReturnsFalseButStillBuffers(): void
    {
        $buffer = new BufferStream(5);

        self::assertEquals(3, $buffer->write('hi '));
        self::assertSame(0, $buffer->write('hello'));
        self::assertEquals('hi hello', (string) $buffer);
        self::assertEquals(4, $buffer->write('test'));
    }
}
