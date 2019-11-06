<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http\Tests\Stream;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;
use Viserio\Contract\Http\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 */
final class NoSeekStreamTest extends MockeryTestCase
{
    public function testCannotSeek(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot seek a NoSeekStream');

        $streamMock = Mockery::mock(new Stream(\fopen('php://temp', 'w')));
        $streamMock->shouldReceive('seek')
            ->never();
        $streamMock->shouldReceive('isSeekable')
            ->never();

        $wrapped = new NoSeekStream($streamMock);

        self::assertFalse($wrapped->isSeekable());

        $wrapped->seek(2);
    }

    public function testToStringDoesNotSeek(): void
    {
        $body = 'foo';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s = new Stream($stream);
        $s->seek(1);

        $wrapped = new NoSeekStream($s);

        self::assertEquals('oo', (string) $wrapped);

        $wrapped->close();
    }
}
