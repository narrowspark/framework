<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Stream\ByteCountingStream;
use Viserio\Http\StreamFactory;

class ByteCountingStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Bytes to read should be a non-negative integer for ByteCountingStream
     */
    public function testEnsureNonNegativeByteCount()
    {
        new ByteCountingStream((new StreamFactory())->createStreamFromString('testing'), -2);
    }

    /**
     * @expectedException \Viserio\Contracts\Http\Exceptions\ByteCountingStreamException
     * @expectedExceptionMessage The ByteCountingStream decorator expects to be able to read
     */
    public function testEnsureValidByteCountNumber()
    {
        new ByteCountingStream((new StreamFactory())->createStreamFromString('testing'), 10);
    }

    public function testByteCountingReadWhenAvailable()
    {
        $testStream = new ByteCountingStream((new StreamFactory())->createStreamFromString('foo bar test'), 8);

        $this->assertEquals('foo ', $testStream->read(4));
        $this->assertEquals('bar ', $testStream->read(4));
        $this->assertEquals('', $testStream->read(4));

        $testStream->close();
        $testStream = new ByteCountingStream((new StreamFactory())->createStreamFromString('testing'), 5);
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
        $testStream = new ByteCountingStream((new StreamFactory())->createStreamFromString('abc'), 3);
        $testStream->seek(3);
        $testStream->read(3);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The stream is detached
     */
    public function testEnsureReadUnclosedStream()
    {
        $body = (new StreamFactory())->createStreamFromString('closed');
        $closedStream = new ByteCountingStream($body, 5);
        $body->close();
        $closedStream->read(3);
    }
}
