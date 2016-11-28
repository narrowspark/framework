<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use RuntimeException;
use Viserio\Http\Stream;
use Viserio\Http\Stream\FnStream;
use Viserio\Http\Stream\LimitStream;
use Viserio\Http\Stream\NoSeekStream;

class LimitStreamTest extends \PHPUnit_Framework_TestCase
{
    /** @var LimitStream */
    protected $body;

    /** @var Stream */
    protected $decorated;

    public function setUp()
    {
        $this->decorated = new Stream(fopen(__FILE__, 'r'));
        $this->body = new LimitStream($this->decorated, 10, 3);
    }

    public function testReturnsSubset()
    {
        $body = 'foo';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $body = new LimitStream(new Stream($stream), -1, 1);

        self::assertEquals('oo', (string) $body);
        self::assertTrue($body->eof());

        $body->seek(0);

        self::assertFalse($body->eof());
        self::assertEquals('oo', $body->read(100));
        self::assertSame('', $body->read(1));
        self::assertTrue($body->eof());
    }

    public function testReturnsSubsetWhenCastToString()
    {
        $body = 'foo_baz_bar';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $limited = new LimitStream(new Stream($stream), 3, 4);

        self::assertEquals('baz', (string) $limited);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to seek to stream position 10 with whence 0
     */
    public function testEnsuresPositionCanBeekSeekedTo()
    {
        new LimitStream(new Stream(fopen('php://temp', 'r+')), 0, 10);
    }

    public function testReturnsSubsetOfEmptyBodyWhenCastToString()
    {
        $body = '01234567891234';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $limited = new LimitStream(new Stream($stream), 0, 10);

        self::assertEquals('', (string) $limited);
    }

    public function testReturnsSpecificSubsetOBodyWhenCastToString()
    {
        $body = '0123456789abcdef';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $limited = new LimitStream(new Stream($stream), 3, 10);

        self::assertEquals('abc', (string) $limited);
    }

    public function testSeeksWhenConstructed()
    {
        self::assertEquals(0, $this->body->tell());
        self::assertEquals(3, $this->decorated->tell());
    }

    public function testAllowsBoundedSeek()
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

    public function testReadsOnlySubsetOfData()
    {
        $data = $this->body->read(100);

        self::assertEquals(10, strlen($data));
        self::assertSame('', $this->body->read(1000));
        $this->body->setOffset(10);

        $newData = $this->body->read(100);

        self::assertEquals(10, strlen($newData));
        self::assertNotSame($data, $newData);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not seek to stream offset 2
     */
    public function testThrowsWhenCurrentGreaterThanOffsetSeek()
    {
        $body = 'foo_bar';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream1 = new Stream($stream);
        $stream2 = new NoSeekStream($stream1);
        $stream3 = new LimitStream($stream2);

        $stream1->getContents();
        $stream3->setOffset(2);
    }

    public function testCanGetContentsWithoutSeeking()
    {
        $body = 'foo_bar';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream1 = new Stream($stream);
        $stream2 = new NoSeekStream($stream1);
        $stream3 = new LimitStream($stream2);

        self::assertEquals('foo_bar', $stream3->getContents());
    }

    public function testClaimsConsumedWhenReadLimitIsReached()
    {
        self::assertFalse($this->body->eof());

        $this->body->read(1000);

        self::assertTrue($this->body->eof());
    }

    public function testContentLengthIsBounded()
    {
        self::assertEquals(10, $this->body->getSize());
    }

    public function testGetContentsIsBasedOnSubset()
    {
        $body = 'foobazbar';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $body = new LimitStream(new Stream($stream), 3, 3);

        self::assertEquals('baz', $body->getContents());
    }

    public function testReturnsNullIfSizeCannotBeDetermined()
    {
        $stream = new FnStream([
            'getSize' => function () {
            },
            'tell' => function () {
                return 0;
            },
        ]);
        $stream2 = new LimitStream($stream);

        self::assertNull($stream2->getSize());
    }

    public function testLengthLessOffsetWhenNoLimitSize()
    {
        $body = 'foo_bar';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $a = new Stream($stream);
        $b = new LimitStream($a, -1, 4);

        self::assertEquals(3, $b->getSize());
    }
}
