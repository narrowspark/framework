<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\Stream\LimitStream;
use Viserio\Component\Http\Stream\NoSeekStream;

class LimitStreamTest extends TestCase
{
    /** @var LimitStream */
    protected $body;

    /** @var Stream */
    protected $decorated;

    public function setUp(): void
    {
        $this->decorated = new Stream(\fopen(__FILE__, 'rb'));
        $this->body      = new LimitStream($this->decorated, 10, 3);
    }

    public function testReturnsSubset(): void
    {
        $body   = 'foo';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $body = new LimitStream(new Stream($stream), -1, 1);

        self::assertEquals('oo', (string) $body);
        self::assertTrue($body->eof());

        $body->seek(0);

        self::assertFalse($body->eof());
        self::assertEquals('oo', $body->read(100));
        self::assertSame('', $body->read(1));
        self::assertTrue($body->eof());
    }

    public function testReturnsSubsetWhenCastToString(): void
    {
        $body   = 'foo_baz_bar';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $limited = new LimitStream(new Stream($stream), 3, 4);

        self::assertEquals('baz', (string) $limited);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to seek to stream position 10 with whence 0
     */
    public function testEnsuresPositionCanBeekSeekedTo(): void
    {
        new LimitStream(new Stream(\fopen('php://temp', 'rb+')), 0, 10);
    }

    public function testReturnsSubsetOfEmptyBodyWhenCastToString(): void
    {
        $body   = '01234567891234';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $limited = new LimitStream(new Stream($stream), 0, 10);

        self::assertEquals('', (string) $limited);
    }

    public function testReturnsSpecificSubsetOBodyWhenCastToString(): void
    {
        $body   = '0123456789abcdef';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $limited = new LimitStream(new Stream($stream), 3, 10);

        self::assertEquals('abc', (string) $limited);
    }

    public function testSeeksWhenConstructed(): void
    {
        self::assertEquals(0, $this->body->tell());
        self::assertEquals(3, $this->decorated->tell());
    }

    public function testAllowsBoundedSeek(): void
    {
        $this->body->seek(100);
        self::assertEquals(10, $this->body->tell());
        self::assertEquals(13, $this->decorated->tell());
        $this->body->seek(0);
        self::assertEquals(0, $this->body->tell());
        self::assertEquals(3, $this->decorated->tell());

        try {
            $this->body->seek(-10);
            $this->fail();
        } catch (RuntimeException $e) {
        }

        self::assertEquals(0, $this->body->tell());
        self::assertEquals(3, $this->decorated->tell());
        $this->body->seek(5);
        self::assertEquals(5, $this->body->tell());
        self::assertEquals(8, $this->decorated->tell());

        // Fail
        try {
            $this->body->seek(1000, SEEK_END);
            $this->fail();
        } catch (RuntimeException $e) {
        }
    }

    public function testReadsOnlySubsetOfData(): void
    {
        $data = $this->body->read(100);

        self::assertEquals(10, \mb_strlen($data));
        self::assertSame('', $this->body->read(1000));
        $this->body->setOffset(10);

        $newData = $this->body->read(100);

        self::assertEquals(10, \mb_strlen($newData));
        self::assertNotSame($data, $newData);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not seek to stream offset 2
     */
    public function testThrowsWhenCurrentGreaterThanOffsetSeek(): void
    {
        $body   = 'foo_bar';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream1 = new Stream($stream);
        $stream2 = new NoSeekStream($stream1);
        $stream3 = new LimitStream($stream2);

        $stream1->getContents();
        $stream3->setOffset(2);
    }

    public function testCanGetContentsWithoutSeeking(): void
    {
        $body   = 'foo_bar';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream1 = new Stream($stream);
        $stream2 = new NoSeekStream($stream1);
        $stream3 = new LimitStream($stream2);

        self::assertEquals('foo_bar', $stream3->getContents());
    }

    public function testClaimsConsumedWhenReadLimitIsReached(): void
    {
        self::assertFalse($this->body->eof());

        $this->body->read(1000);

        self::assertTrue($this->body->eof());
    }

    public function testContentLengthIsBounded(): void
    {
        self::assertEquals(10, $this->body->getSize());
    }

    public function testGetContentsIsBasedOnSubset(): void
    {
        $body   = 'foobazbar';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $body = new LimitStream(new Stream($stream), 3, 3);

        self::assertEquals('baz', $body->getContents());
    }

    public function testReturnsNullIfSizeCannotBeDetermined(): void
    {
        $stream = new FnStream([
            'getSize' => function (): void {
            },
            'tell' => function () {
                return 0;
            },
        ]);
        $stream2 = new LimitStream($stream);

        self::assertNull($stream2->getSize());
    }

    public function testLengthLessOffsetWhenNoLimitSize(): void
    {
        $body   = 'foo_bar';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $a = new Stream($stream);
        $b = new LimitStream($a, -1, 4);

        self::assertEquals(3, $b->getSize());
    }
}
