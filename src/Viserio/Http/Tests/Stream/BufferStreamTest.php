<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Stream\BufferStream;

class BufferStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testHasMetadata()
    {
        $buffer = new BufferStream(10);

        $this->assertTrue($buffer->isReadable());
        $this->assertTrue($buffer->isWritable());
        $this->assertFalse($buffer->isSeekable());
        $this->assertEquals(null, $buffer->getMetadata('foo'));
        $this->assertEquals(10, $buffer->getMetadata('hwm'));
        $this->assertEquals([], $buffer->getMetadata());
    }

    public function testRemovesReadDataFromBuffer()
    {
        $buffer = new BufferStream();

        $this->assertEquals(3, $buffer->write('foo'));
        $this->assertEquals(3, $buffer->getSize());
        $this->assertFalse($buffer->eof());
        $this->assertEquals('foo', $buffer->read(10));
        $this->assertTrue($buffer->eof());
        $this->assertEquals('', $buffer->read(10));
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

        $this->assertEquals('foo', $buffer->read(3));

        $buffer->write('bar');

        $this->assertEquals('bazbar', (string) $buffer);
        $buffer->tell();
    }

    public function testDetachClearsBuffer()
    {
        $buffer = new BufferStream();
        $buffer->write('foo');
        $buffer->detach();

        $this->assertTrue($buffer->eof());
        $this->assertEquals(3, $buffer->write('abc'));
        $this->assertEquals('abc', $buffer->read(10));
    }

    public function testExceedingHighwaterMarkReturnsFalseButStillBuffers()
    {
        $buffer = new BufferStream(5);

        $this->assertEquals(3, $buffer->write('hi '));
        $this->assertSame(0, $buffer->write('hello'));
        $this->assertEquals('hi hello', (string) $buffer);
        $this->assertEquals(4, $buffer->write('test'));
    }
}
