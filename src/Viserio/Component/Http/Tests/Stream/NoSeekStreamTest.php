<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Http\Tests\Stream;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\NoSeekStream;
use Viserio\Contract\Http\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class NoSeekStreamTest extends MockeryTestCase
{
    public function testCannotSeek(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot seek a NoSeekStream');

        /** @var resource $handle */
        $handle = \fopen('php://temp', 'wb');

        /** @var \Mockery\MockInterface|StreamInterface $streamMock */
        $streamMock = Mockery::mock(new Stream($handle));
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
        /** @var resource $handle */
        $handle = \fopen('php://temp', 'r+b');

        \fwrite($handle, $body);
        \fseek($handle, 0);

        $s = new Stream($handle);
        $s->seek(1);

        $wrapped = new NoSeekStream($s);

        self::assertEquals('oo', (string) $wrapped);

        $wrapped->close();
    }
}
