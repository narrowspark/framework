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

use BadMethodCallException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Contract\Http\Exception\LogicException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class FnStreamTest extends TestCase
{
    public function testDoNotAllowUnserialization(): void
    {
        $this->expectException(LogicException::class);

        $a = new FnStream([]);
        $b = \serialize($a);

        \unserialize($b);
    }

    public function testThrowsWhenNotImplemented(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('seek() is not implemented in the FnStream');

        (new FnStream([]))->seek(1);
    }

    public function testProxiesToFunction(): void
    {
        $stream = new FnStream([
            'read' => function (int $len): string {
                Assert::assertEquals(3, $len);

                return 'foo';
            },
        ]);

        self::assertEquals('foo', $stream->read(3));
    }

    public function testCanCloseOnDestruct(): void
    {
        $called = false;

        $stream = new FnStream([
            'close' => static function () use (&$called): void {
                $called = true;
            },
        ]);
        unset($stream);

        self::assertTrue($called);
    }

    public function doesNotRequireClose(): void
    {
        $stream = new FnStream([]);

        unset($stream);
    }

    public function testDecoratesStream(): void
    {
        $body = 'foo';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $stream1 = new Stream($handler);
        $stream2 = FnStream::decorate($stream1, []);

        self::assertEquals(3, $stream2->getSize());
        self::assertEquals($stream2->isWritable(), true);
        self::assertEquals($stream2->isReadable(), true);
        self::assertEquals($stream2->isSeekable(), true);
        self::assertEquals($stream2->read(3), 'foo');
        self::assertEquals($stream2->tell(), 3);
        self::assertEquals($stream1->tell(), 3);
        self::assertSame('', $stream1->read(1));
        self::assertEquals($stream2->eof(), true);
        self::assertEquals($stream1->eof(), true);

        $stream2->seek(0);

        self::assertEquals('foo', (string) $stream2);

        $stream2->seek(0);

        self::assertEquals('foo', $stream2->getContents());
        self::assertEquals($stream1->getMetadata(), $stream2->getMetadata());

        $stream2->seek(0, \SEEK_END);
        $stream2->write('bar');

        self::assertEquals('foobar', (string) $stream2);
        self::assertIsResource($stream2->detach());

        $stream2->close();
    }

    public function testDecoratesWithCustomizations(): void
    {
        $called = false;

        $body = 'foo';
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $stream1 = new Stream($handler);
        $stream2 = FnStream::decorate($stream1, [
            'read' => static function (int $len) use (&$called, $stream1): string {
                $called = true;

                return $stream1->read($len);
            },
        ]);

        self::assertEquals('foo', $stream2->read(3));
        self::assertTrue($called);
    }
}
