<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Stream\ByteCountingStream;
use Viserio\Http\Stream;

class ByteCountingStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Bytes to read should be a non-negative integer for ByteCountingStream
     */
    public function testEnsureNonNegativeByteCount()
    {
        $body = 'testing';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        new ByteCountingStream(new Stream($stream), -2);
    }

    /**
     * @expectedException \Viserio\Contracts\Http\Exceptions\ByteCountingStreamException
     * @expectedExceptionMessage The ByteCountingStream decorator expects to be able to read
     */
    public function testEnsureValidByteCountNumber()
    {
        $body = 'testing';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        new ByteCountingStream(new Stream($stream), 10);
    }

    public function testByteCountingReadWhenAvailable()
    {
        $body = 'foo bar test';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $testStream = new ByteCountingStream(new Stream($stream), 8);

        $this->assertEquals('foo ', $testStream->read(4));
        $this->assertEquals('bar ', $testStream->read(4));
        $this->assertEquals('', $testStream->read(4));

        $body = 'testing';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $testStream->close();
        $testStream = new ByteCountingStream(new Stream($stream), 5);
        $testStream->seek(4);

        $this->assertEquals('ing', $testStream->read(5));

        $testStream->close();
    }

    /**
     * @expectedException \Viserio\Contracts\Http\Exceptions\ByteCountingStreamException
     * @expectedExceptionMessage The ByteCountingStream decorator expects to be able to read
     */
    public function testEnsureStopReadWhenHitEof()
    {
        $body = 'abc';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $testStream = new ByteCountingStream(new Stream($stream), 3);
        $testStream->seek(3);
        $testStream->read(3);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The stream is detached
     */
    public function testEnsureReadUnclosedStream()
    {
        $body = 'closed';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $body = new Stream($stream);
        $closedStream = new ByteCountingStream($body, 5);
        $body->close();
        $closedStream->read(3);
    }
}
