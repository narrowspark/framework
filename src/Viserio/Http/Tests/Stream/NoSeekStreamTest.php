<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Psr\Http\Message\StreamInterface;
use Viserio\Http\Stream;
use Viserio\Http\Stream\NoSeekStream;

class NoSeekStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot seek a NoSeekStream
     */
    public function testCannotSeek()
    {
        $s = $this->getMockBuilder(StreamInterface::class)
            ->setMethods(['isSeekable', 'seek'])
            ->getMockForAbstractClass();

        $s->expects($this->never())->method('seek');
        $s->expects($this->never())->method('isSeekable');

        $wrapped = new NoSeekStream($s);
        self::assertFalse($wrapped->isSeekable());
        $wrapped->seek(2);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot write to a non-writable stream
     */
    public function testHandlesClose()
    {
        $body   = 'foo';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $s       = new Stream($stream);
        $wrapped = new NoSeekStream($s);
        $wrapped->close();
        $wrapped->write('foo');
    }
}
