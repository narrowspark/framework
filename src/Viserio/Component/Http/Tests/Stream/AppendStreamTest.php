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
use Viserio\Component\Http\Stream\AppendStream;
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Contract\Http\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 */
final class AppendStreamTest extends MockeryTestCase
{
    public function testValidatesStreamsAreReadable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each stream must be readable');

        $appendStream = new AppendStream();

        /** @var \Mockery\MockInterface|\Viserio\Component\Http\Stream $stream */
        $stream = Mockery::mock(new Stream(\fopen('php://temp', 'w')));
        $stream->shouldReceive('isReadable')
            ->andReturn(false);

        $appendStream->addStream($stream);

        $stream->close();
    }

    public function testValidatesSeekType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The AppendStream can only seek with SEEK_SET');

        $a = new AppendStream();
        $a->seek(100, \SEEK_CUR);
    }

    public function testTriesToRewindOnSeek(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to seek stream 0 of the AppendStream');

        $a = new AppendStream();
        $stream = Mockery::mock(new Stream(\fopen('php://temp', 'w')));
        $stream->shouldReceive('isReadable')
            ->andReturn(true);
        $stream->shouldReceive('isSeekable')
            ->andReturn(true);
        $stream->shouldReceive('rewind')
            ->andThrow(new RuntimeException());

        $a->addStream($stream);
        $a->seek(10);
    }

    public function testSeeksToPositionByReading(): void
    {
        $a = new AppendStream([
            Util::createStreamFor('foo'),
            Util::createStreamFor('bar'),
            Util::createStreamFor('baz'),
        ]);

        $a->seek(3);
        self::assertEquals(3, $a->tell());
        self::assertEquals('bar', $a->read(3));

        $a->seek(6);
        self::assertEquals(6, $a->tell());
        self::assertEquals('baz', $a->read(3));
    }

    public function testDetachWithoutStreams(): void
    {
        $s = new AppendStream();
        $s->detach();

        self::assertSame(0, $s->getSize());
        self::assertTrue($s->eof());
        self::assertTrue($s->isReadable());
        self::assertSame('', (string) $s);
        self::assertTrue($s->isSeekable());
        self::assertFalse($s->isWritable());
    }

    public function testDetachesEachStream(): void
    {
        $handle = \fopen('php://temp', 'r');

        $s1 = Util::createStreamFor($handle);
        $s2 = Util::createStreamFor('bar');
        $a = new AppendStream([$s1, $s2]);

        $a->detach();

        self::assertSame(0, $a->getSize());
        self::assertTrue($a->eof());
        self::assertTrue($a->isReadable());
        self::assertSame('', (string) $a);
        self::assertTrue($a->isSeekable());
        self::assertFalse($a->isWritable());

        self::assertNull($s1->detach());
        self::assertIsResource($handle, 'resource is not closed when detaching');

        fclose($handle);
    }

    public function testClosesEachStream(): void
    {
        $handle = \fopen('php://temp', 'r');

        $s1 = Util::createStreamFor($handle);
        $s2 = Util::createStreamFor('bar');
        $a = new AppendStream([$s1, $s2]);

        $a->close();

        self::assertSame(0, $a->getSize());
        self::assertTrue($a->eof());
        self::assertTrue($a->isReadable());
        self::assertSame('', (string) $a);
        self::assertTrue($a->isSeekable());
        self::assertFalse($a->isWritable());

        self::assertIsResource($handle);
    }

    public function testIsNotWritable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot write to an AppendStream');

        $a = new AppendStream([Util::createStreamFor('foo')]);

        self::assertFalse($a->isWritable());
        self::assertTrue($a->isSeekable());
        self::assertTrue($a->isReadable());

        $a->write('foo');
    }

    public function testDoesNotNeedStreams(): void
    {
        $a = new AppendStream();

        self::assertEquals('', (string) $a);
    }

    public function testCanReadFromMultipleStreams(): void
    {
        $a = new AppendStream([
            Util::createStreamFor('foo'),
            Util::createStreamFor('bar'),
            Util::createStreamFor('baz'),
        ]);

        self::assertFalse($a->eof());
        self::assertSame(0, $a->tell());
        self::assertEquals('foo', $a->read(3));
        self::assertEquals('bar', $a->read(3));
        self::assertEquals('baz', $a->read(3));
        self::assertSame('', $a->read(1));
        self::assertTrue($a->eof());
        self::assertSame(9, $a->tell());
        self::assertEquals('foobarbaz', (string) $a);
    }

    public function testCanDetermineSizeFromMultipleStreams(): void
    {
        $a = new AppendStream([
            Util::createStreamFor('foo'),
            Util::createStreamFor('bar'),
        ]);

        self::assertEquals(6, $a->getSize());

        $streamMock = Mockery::mock(new Stream(\fopen('php://temp', 'r')));
        $streamMock->shouldReceive('isSeekable')
            ->andReturn(false);
        $streamMock->shouldReceive('getSize')
            ->andReturn(null);

        $a->addStream($streamMock);

        self::assertNull($a->getSize());
    }

    public function testReturnsEmptyMetadata(): void
    {
        $s = new AppendStream();

        self::assertEquals([], $s->getMetadata());
        self::assertNull($s->getMetadata('foo'));
    }

    /**
     * Make sure expectException always exists, even on PHPUnit 4.
     *
     * @param string      $exception
     * @param null|string $message
     */
    public function expectException($exception, $message = null): void
    {
        parent::expectException($exception);

        if (null !== $message) {
            $this->expectExceptionMessage($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
