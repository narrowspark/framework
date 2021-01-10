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

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\Stream\LimitStream;
use Viserio\Component\Http\Stream\NoSeekStream;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class LimitStreamTest extends TestCase
{
    /** @var LimitStream */
    protected $body;

    /** @var Stream */
    protected $decorated;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        /** @var resource $handler */
        $handler = \fopen(__FILE__, 'rb');

        $this->decorated = new Stream($handler);
        $this->body = new LimitStream($this->decorated, 10, 3);
    }

    public function testReturnsSubset(): void
    {
        $body = 'foo';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $body = new LimitStream(new Stream($handler), -1, 1);

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
        $body = 'foo_baz_bar';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $limited = new LimitStream(new Stream($handler), 3, 4);

        self::assertEquals('baz', (string) $limited);
    }

    public function testEnsuresPositionCanBeekSeekedTo(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to seek to stream position 10 with whence 0');

        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        new LimitStream(new Stream($handler), 0, 10);
    }

    public function testReturnsSubsetOfEmptyBodyWhenCastToString(): void
    {
        $body = '01234567891234';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $limited = new LimitStream(new Stream($handler), 0, 10);

        self::assertEquals('', (string) $limited);
    }

    public function testReturnsSpecificSubsetOBodyWhenCastToString(): void
    {
        $body = '0123456789abcdef';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $limited = new LimitStream(new Stream($handler), 3, 10);

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
            self::fail();
        } catch (RuntimeException $e) {
            // @ignoreException
        }

        self::assertEquals(0, $this->body->tell());
        self::assertEquals(3, $this->decorated->tell());

        $this->body->seek(5);

        self::assertEquals(5, $this->body->tell());
        self::assertEquals(8, $this->decorated->tell());

        // Fail
        try {
            $this->body->seek(1000, \SEEK_END);
            self::fail();
        } catch (RuntimeException $e) {
            // @ignoreException
        }
    }

    public function testReadsOnlySubsetOfData(): void
    {
        $data = $this->body->read(100);

        self::assertEquals(10, \strlen($data));
        self::assertSame('', $this->body->read(1000));

        $this->body->setOffset(10);
        $newData = $this->body->read(100);

        self::assertEquals(10, \strlen($newData));
        self::assertNotSame($data, $newData);
    }

    public function testThrowsWhenCurrentGreaterThanOffsetSeek(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not seek to stream offset 2');

        $body = 'foo_bar';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $stream1 = new Stream($handler);
        $stream2 = new NoSeekStream($stream1);
        $stream3 = new LimitStream($stream2);

        $stream1->getContents();
        $stream3->setOffset(2);
    }

    public function testCanGetContentsWithoutSeeking(): void
    {
        $body = 'foo_bar';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $stream1 = new Stream($handler);
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
        $body = 'foobazbar';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $body = new LimitStream(new Stream($handler), 3, 3);

        self::assertEquals('baz', $body->getContents());
    }

    public function testReturnsNullIfSizeCannotBeDetermined(): void
    {
        $stream = new FnStream([
            'getSize' => static function (): void {
            },
            'tell' => static function (): int {
                return 0;
            },
        ]);
        $stream2 = new LimitStream($stream);

        self::assertNull($stream2->getSize());
    }

    public function testLengthLessOffsetWhenNoLimitSize(): void
    {
        $body = 'foo_bar';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $a = new Stream($handler);
        $b = new LimitStream($a, -1, 4);

        self::assertEquals(3, $b->getSize());
    }
}
