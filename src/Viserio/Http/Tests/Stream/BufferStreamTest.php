<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Stream\BufferStream;

class BufferStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testHasMetadata()
    {
        $buffer = new BufferStream(10);

        self::assertTrue($buffer->isReadable());
        self::assertTrue($buffer->isWritable());
        self::assertFalse($buffer->isSeekable());
        self::assertEquals(null, $buffer->getMetadata('foo'));
        self::assertEquals(10, $buffer->getMetadata('hwm'));
        self::assertEquals([], $buffer->getMetadata());
    }

    public function testRemovesReadDataFromBuffer()
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
    public function testCanCastToStringOrGetContents()
    {
        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->write('baz');

        self::assertEquals('foo', $buffer->read(3));

        $buffer->write('bar');

        self::assertEquals('bazbar', (string) $buffer);
        $buffer->tell();
    }

    public function testDetachClearsBuffer()
    {
        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->detach();

        self::assertTrue($buffer->eof());
        self::assertEquals(3, $buffer->write('abc'));
        self::assertEquals('abc', $buffer->read(10));
    }

    public function testExceedingHighwaterMarkReturnsFalseButStillBuffers()
    {
        $buffer = new BufferStream(5);

        self::assertEquals(3, $buffer->write('hi '));
        self::assertSame(0, $buffer->write('hello'));
        self::assertEquals('hi hello', (string) $buffer);
        self::assertEquals(4, $buffer->write('test'));
    }
}
