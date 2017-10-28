<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;

class NoSeekStreamTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot seek a NoSeekStream
     */
    public function testCannotSeek(): void
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

    public function testToStringDoesNotSeek(): void
    {
        $body   = 'foo';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s = new Stream($stream);
        $s->seek(1);

        $wrapped = new NoSeekStream($s);

        self::assertEquals('oo', (string) $wrapped);

        $wrapped->close();
    }
}
