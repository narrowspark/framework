<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;

/**
 * @internal
 */
final class NoSeekStreamTest extends TestCase
{
    public function testCannotSeek(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot seek a NoSeekStream');

        $s = $this->getMockBuilder(StreamInterface::class)
            ->setMethods(['isSeekable', 'seek'])
            ->getMockForAbstractClass();

        $s->expects(static::never())->method('seek');
        $s->expects(static::never())->method('isSeekable');

        $wrapped = new NoSeekStream($s);
        static::assertFalse($wrapped->isSeekable());
        $wrapped->seek(2);
    }

    public function testToStringDoesNotSeek(): void
    {
        $body   = 'foo';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s = new Stream($stream);
        $s->seek(1);

        $wrapped = new NoSeekStream($s);

        static::assertEquals('oo', (string) $wrapped);

        $wrapped->close();
    }
}
