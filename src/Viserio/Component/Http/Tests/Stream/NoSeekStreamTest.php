<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Http\Exception\RuntimeException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;

/**
 * @internal
 */
final class NoSeekStreamTest extends MockeryTestCase
{
    public function testCannotSeek(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot seek a NoSeekStream');

        $streamMock = $this->mock(new Stream(\fopen('php://temp', 'w')));
        $streamMock->shouldReceive('seek')
            ->never();
        $streamMock->shouldReceive('isSeekable')
            ->never();

        $wrapped = new NoSeekStream($streamMock);

        $this->assertFalse($wrapped->isSeekable());

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

        $this->assertEquals('oo', (string) $wrapped);

        $wrapped->close();
    }
}
