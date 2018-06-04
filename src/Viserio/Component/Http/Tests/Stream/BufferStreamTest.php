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

        $this->assertTrue($buffer->isReadable());
        $this->assertTrue($buffer->isWritable());
        $this->assertFalse($buffer->isSeekable());
        $this->assertNull($buffer->getMetadata('foo'));
        $this->assertEquals(10, $buffer->getMetadata('hwm'));
        $this->assertEquals([], $buffer->getMetadata());
    }

    public function testRemovesReadDataFromBuffer(): void
    {
        $buffer = new BufferStream();

        $this->assertEquals(3, $buffer->write('foo'));
        $this->assertEquals(3, $buffer->getSize());
        $this->assertFalse($buffer->eof());
        $this->assertEquals('foo', $buffer->read(10));
        $this->assertTrue($buffer->eof());
        $this->assertEquals('', $buffer->read(10));
    }

    public function testCanCastToStringOrGetContents(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot determine the position of a BufferStream');

        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->write('baz');

        $this->assertEquals('foo', $buffer->read(3));

        $buffer->write('bar');

        $this->assertEquals('bazbar', (string) $buffer);
        $buffer->tell();
    }

    public function testDetachClearsBuffer(): void
    {
        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->detach();

        $this->assertTrue($buffer->eof());
        $this->assertEquals(3, $buffer->write('abc'));
        $this->assertEquals('abc', $buffer->read(10));
    }

    public function testExceedingHighwaterMarkReturnsFalseButStillBuffers(): void
    {
        $buffer = new BufferStream(5);

        $this->assertEquals(3, $buffer->write('hi '));
        $this->assertSame(0, $buffer->write('hello'));
        $this->assertEquals('hi hello', (string) $buffer);
        $this->assertEquals(4, $buffer->write('test'));
    }
}
