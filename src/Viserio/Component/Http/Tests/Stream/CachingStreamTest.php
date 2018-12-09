<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Nyholm\NSA;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\CachingStream;
use Viserio\Component\Http\Util;

/**
 * @internal
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
        $this->body      = new CachingStream($this->decorated);
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
        $body    = Util::createStreamFor('test');
        $caching = new CachingStream($body);

        $this->assertEquals(4, $caching->getSize());
    }

    public function testReadsUntilCachedToByte(): void
    {
        $this->body->seek(5);

        $this->assertEquals('n', $this->body->read(1));

        $this->body->seek(0);

        $this->assertEquals('t', $this->body->read(1));
    }

    public function testCanSeekNearEndWithSeekEnd(): void
    {
        $baseStream = Util::createStreamFor(\implode('', \range('a', 'z')));

        $cached = new CachingStream($baseStream);
        $cached->seek(-1, \SEEK_END);

        $this->assertEquals(25, $baseStream->tell());
        $this->assertEquals('z', $cached->read(1));
        $this->assertEquals(26, $cached->getSize());
    }

    public function testCanSeekToEndWithSeekEnd(): void
    {
        $baseStream = Util::createStreamFor(\implode('', \range('a', 'z')));
        $cached     = new CachingStream($baseStream);
        $cached->seek(0, \SEEK_END);

        $this->assertEquals(26, $baseStream->tell());
        $this->assertEquals('', $cached->read(1));
        $this->assertEquals(26, $cached->getSize());
    }

    public function testCanUseSeekEndWithUnknownSize(): void
    {
        $baseStream = Util::createStreamFor('testing');
        $decorated  = Stream\FnStream::decorate($baseStream, [
            'getSize' => function () {
                return null;
            },
        ]);
        $cached = new CachingStream($decorated);
        $cached->seek(-1, \SEEK_END);

        $this->assertEquals('g', $cached->read(1));
    }

    public function testRewindUsesSeek(): void
    {
        $a = Util::createStreamFor('foo');

        $stream = $this->mock(CachingStream::class . '[seek]', [$a]);
        $stream->shouldReceive('seek')
            ->with(0)
            ->andReturn(true);

        $stream->seek(0);
    }

    public function testCanSeekToReadBytes(): void
    {
        $this->assertEquals('te', $this->body->read(2));

        $this->body->seek(0);

        $this->assertEquals('test', $this->body->read(4));
        $this->assertEquals(4, $this->body->tell());

        $this->body->seek(2);

        $this->assertEquals(2, $this->body->tell());

        $this->body->seek(2, \SEEK_CUR);

        $this->assertEquals(4, $this->body->tell());
        $this->assertEquals('ing', $this->body->read(3));
    }

    public function testCanSeekToReadBytesWithPartialBodyReturned(): void
    {
        $stream = \fopen('php://temp', 'r+');
        \fwrite($stream, 'testing');
        \fseek($stream, 0);

        $this->decorated = $this->mock(Stream::class . '[read]', [$stream]);
        $this->decorated->shouldReceive('read')
            ->andReturnUsing(function ($length) use ($stream) {
                return fread($stream, $length);
            });

        $this->body = new CachingStream($this->decorated);

        $this->assertEquals(0, $this->body->tell());
        $this->body->seek(4, \SEEK_SET);
        $this->assertEquals(4, $this->body->tell());

        $this->body->seek(0);
        $this->assertEquals('test', $this->body->read(4));
    }

    public function testWritesToBufferStream(): void
    {
        $this->body->read(2);
        $this->body->write('hi');
        $this->body->seek(0);
        $this->assertEquals('tehiing', (string) $this->body);
    }

    public function testSkipsOverwrittenBytes(): void
    {
        $decorated = Util::createStreamFor(
            \implode("\n", \array_map(function ($n) {
                return \str_pad((string) $n, 4, '0', \STR_PAD_LEFT);
            }, \range(0, 25)))
        );

        $body = new CachingStream($decorated);

        $this->assertEquals("0000\n", Util::readline($body));
        $this->assertEquals("0001\n", Util::readline($body));
        // Write over part of the body yet to be read, so skip some bytes
        $this->assertEquals(5, $body->write("TEST\n"));
        $this->assertEquals(5, NSA::getProperty($body, 'skipReadBytes'));
        // Read, which skips bytes, then reads
        $this->assertEquals("0003\n", Util::readline($body));
        $this->assertEquals(0, NSA::getProperty($body, 'skipReadBytes'));
        $this->assertEquals("0004\n", Util::readline($body));
        $this->assertEquals("0005\n", Util::readline($body));

        // Overwrite part of the cached body (so don't skip any bytes)
        $body->seek(5);
        $this->assertEquals(5, $body->write("ABCD\n"));
        $this->assertEquals(0, NSA::getProperty($body, 'skipReadBytes'));
        $this->assertEquals("TEST\n", Util::readline($body));
        $this->assertEquals("0003\n", Util::readline($body));
        $this->assertEquals("0004\n", Util::readline($body));
        $this->assertEquals("0005\n", Util::readline($body));
        $this->assertEquals("0006\n", Util::readline($body));
        $this->assertEquals(5, $body->write("1234\n"));
        $this->assertEquals(5, NSA::getProperty($body, 'skipReadBytes'));

        // Seek to 0 and ensure the overwritten bit is replaced
        $body->seek(0);
        $this->assertEquals("0000\nABCD\nTEST\n0003\n0004\n0005\n0006\n1234\n0008\n0009\n", $body->read(50));

        // Ensure that casting it to a string does not include the bit that was overwritten
        $this->assertContains("0000\nABCD\nTEST\n0003\n0004\n0005\n0006\n1234\n0008\n0009\n", (string) $body);
    }

    public function testClosesBothStreams(): void
    {
        $stream = \fopen('php://temp', 'r');

        $caching = new CachingStream(Util::createStreamFor($stream));
        $caching->close();

        $test = \is_resource($stream);

        $this->assertFalse($test);
    }

    public function testEnsuresValidWhence(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->body->seek(10, -123456);
    }
}
