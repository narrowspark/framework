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
use Nyholm\NSA;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\CachingStream;
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class CachingStreamTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Http\Stream\CachingStream */
    private $body;

    /** @var \Psr\Http\Message\StreamInterface */
    private $decorated;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->decorated = Util::createStreamFor('testing');
        $this->body = new CachingStream($this->decorated);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->decorated->close();
        $this->body->close();
    }

    public function testUsesRemoteSizeIfPossible(): void
    {
        $body = Util::createStreamFor('test');
        $caching = new CachingStream($body);

        self::assertEquals(4, $caching->getSize());
    }

    public function testReadsUntilCachedToByte(): void
    {
        $this->body->seek(5);

        self::assertEquals('n', $this->body->read(1));

        $this->body->seek(0);

        self::assertEquals('t', $this->body->read(1));
    }

    public function testCanSeekNearEndWithSeekEnd(): void
    {
        $baseStream = Util::createStreamFor(\implode('', \range('a', 'z')));

        $cached = new CachingStream($baseStream);
        $cached->seek(-1, \SEEK_END);

        self::assertEquals(25, $baseStream->tell());
        self::assertEquals('z', $cached->read(1));
        self::assertEquals(26, $cached->getSize());
    }

    public function testCanSeekToEndWithSeekEnd(): void
    {
        $baseStream = Util::createStreamFor(\implode('', \range('a', 'z')));
        $cached = new CachingStream($baseStream);
        $cached->seek(0, \SEEK_END);

        self::assertEquals(26, $baseStream->tell());
        self::assertEquals('', $cached->read(1));
        self::assertEquals(26, $cached->getSize());
    }

    public function testCanUseSeekEndWithUnknownSize(): void
    {
        $baseStream = Util::createStreamFor('testing');
        $decorated = Stream\FnStream::decorate($baseStream, [
            'getSize' => static function () {
                return null;
            },
        ]);
        $cached = new CachingStream($decorated);
        $cached->seek(-1, \SEEK_END);

        self::assertEquals('g', $cached->read(1));
    }

    public function testRewindUsesSeek(): void
    {
        $a = Util::createStreamFor('foo');

        $stream = Mockery::mock(CachingStream::class . '[seek]', [$a]);
        $stream->shouldReceive('seek')
            ->with(0)
            ->andReturn(true);

        $stream->seek(0);
    }

    public function testCanSeekToReadBytes(): void
    {
        self::assertEquals('te', $this->body->read(2));

        $this->body->seek(0);

        self::assertEquals('test', $this->body->read(4));
        self::assertEquals(4, $this->body->tell());

        $this->body->seek(2);

        self::assertEquals(2, $this->body->tell());

        $this->body->seek(2, \SEEK_CUR);

        self::assertEquals(4, $this->body->tell());
        self::assertEquals('ing', $this->body->read(3));
    }

    public function testCanSeekToReadBytesWithPartialBodyReturned(): void
    {
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+');

        \fwrite($handler, 'testing');
        \fseek($handler, 0);

        $this->decorated = Mockery::mock(Stream::class . '[read]', [$handler]);
        $this->decorated->shouldReceive('read')
            ->andReturnUsing(static function (int $length) use ($handler): string {
                return (string) fread($handler, $length);
            });

        $this->body = new CachingStream($this->decorated);

        self::assertEquals(0, $this->body->tell());

        $this->body->seek(4, \SEEK_SET);

        self::assertEquals(4, $this->body->tell());

        $this->body->seek(0);

        self::assertEquals('test', $this->body->read(4));
    }

    public function testWritesToBufferStream(): void
    {
        $this->body->read(2);
        $this->body->write('hi');
        $this->body->seek(0);

        self::assertEquals('tehiing', (string) $this->body);
    }

    public function testSkipsOverwrittenBytes(): void
    {
        $decorated = Util::createStreamFor(
            \implode("\n", \array_map(static function (int $n): string {
                return \str_pad((string) $n, 4, '0', \STR_PAD_LEFT);
            }, \range(0, 25)))
        );

        $body = new CachingStream($decorated);

        self::assertEquals("0000\n", Util::readline($body));
        self::assertEquals("0001\n", Util::readline($body));
        // Write over part of the body yet to be read, so skip some bytes
        self::assertEquals(5, $body->write("TEST\n"));
        self::assertEquals(5, NSA::getProperty($body, 'skipReadBytes'));
        // Read, which skips bytes, then reads
        self::assertEquals("0003\n", Util::readline($body));
        self::assertEquals(0, NSA::getProperty($body, 'skipReadBytes'));
        self::assertEquals("0004\n", Util::readline($body));
        self::assertEquals("0005\n", Util::readline($body));

        // Overwrite part of the cached body (so don't skip any bytes)
        $body->seek(5);

        self::assertEquals(5, $body->write("ABCD\n"));
        self::assertEquals(0, NSA::getProperty($body, 'skipReadBytes'));
        self::assertEquals("TEST\n", Util::readline($body));
        self::assertEquals("0003\n", Util::readline($body));
        self::assertEquals("0004\n", Util::readline($body));
        self::assertEquals("0005\n", Util::readline($body));
        self::assertEquals("0006\n", Util::readline($body));
        self::assertEquals(5, $body->write("1234\n"));
        self::assertEquals(5, NSA::getProperty($body, 'skipReadBytes'));

        // Seek to 0 and ensure the overwritten bit is replaced
        $body->seek(0);

        self::assertEquals("0000\nABCD\nTEST\n0003\n0004\n0005\n0006\n1234\n0008\n0009\n", $body->read(50));

        // Ensure that casting it to a string does not include the bit that was overwritten
        self::assertStringContainsString("0000\nABCD\nTEST\n0003\n0004\n0005\n0006\n1234\n0008\n0009\n", (string) $body);
    }

    public function testClosesBothStreams(): void
    {
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r');

        $caching = new CachingStream(Util::createStreamFor($handler));
        $caching->close();

        /** @var null|resource $h */
        $h = $handler;

        self::assertSame(\is_resource($h), false);
    }

    public function testEnsuresValidWhence(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->body->seek(10, -123456);
    }
}
