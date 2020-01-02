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

use ArrayIterator;
use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\LimitStream;
use Viserio\Component\Http\Stream\PumpStream;
use Viserio\Component\Http\Util;

/**
 * @internal
 *
 * @small
 */
final class PumpStreamTest extends TestCase
{
    public function testHasMetadataAndSize(): void
    {
        $pump = new PumpStream(static function (): void {
        }, [
            'metadata' => ['foo' => 'bar'],
            'size' => 100,
        ]);

        self::assertEquals('bar', $pump->getMetadata('foo'));
        self::assertEquals(['foo' => 'bar'], $pump->getMetadata());
        self::assertEquals(100, $pump->getSize());
    }

    public function testCanReadFromCallable(): void
    {
        $pump = new PumpStream(static function (): string {
            return 'a';
        });

        self::assertEquals('a', $pump->read(1));
        self::assertEquals(1, $pump->tell());
        self::assertEquals('aaaaa', $pump->read(5));
        self::assertEquals(6, $pump->tell());
    }

    public function testStoresExcessDataInBuffer(): void
    {
        $called = [];

        $pump = new PumpStream(static function (int $size) use (&$called): string {
            $called[] = $size;

            return 'abcdef';
        });

        self::assertEquals('a', $pump->read(1));
        self::assertEquals('b', $pump->read(1));
        self::assertEquals('cdef', $pump->read(4));
        self::assertEquals('abcdefabc', $pump->read(9));
        self::assertEquals([1, 9, 3], $called);
    }

    public function testInifiniteStreamWrappedInLimitStream(): void
    {
        $pump = new PumpStream(static function (): string {
            return 'a';
        });
        $s = new LimitStream($pump, 5);

        self::assertEquals('aaaaa', (string) $s);
    }

    public function testDescribesCapabilities(): void
    {
        $pump = new PumpStream(static function (): void {
        });

        self::assertTrue($pump->isReadable());
        self::assertFalse($pump->isSeekable());
        self::assertFalse($pump->isWritable());
        self::assertNull($pump->getSize());
        self::assertEquals('', $pump->getContents());
        self::assertEquals('', (string) $pump);

        $pump->close();

        self::assertEquals('', $pump->read(10));
        self::assertTrue($pump->eof());

        try {
            $pump->write('aa');
            self::fail();
        } catch (\RuntimeException $e) {
            // @ignoreException
        }
    }

    public function testCanCreateCallableBasedStream(): void
    {
        $resource = new ArrayIterator(['foo', 'bar', '123']);

        $stream = new PumpStream(static function () use ($resource) {
            if (! $resource->valid()) {
                return false;
            }

            $result = $resource->current();
            $resource->next();

            return $result;
        });

        self::assertEquals('foo', $stream->read(3));
        self::assertFalse($stream->eof());
        self::assertEquals('b', $stream->read(1));
        self::assertEquals('a', $stream->read(1));
        self::assertEquals('r12', $stream->read(3));
        self::assertFalse($stream->eof());
        self::assertEquals('3', $stream->getContents());
        self::assertTrue($stream->eof());
        self::assertEquals(9, $stream->tell());
    }

    public function testThatConvertingStreamToStringWillTriggerErrorAndWillReturnEmptyString(): void
    {
        $p = Util::createStreamFor(static function (): void {
            throw new Exception();
        });

        self::assertInstanceOf(PumpStream::class, $p);

        $errors = [];

        \set_error_handler(function (int $errorNumber, string $errorMessage) use (&$errors): bool {
            $errors[] = ['number' => $errorNumber, 'message' => $errorMessage];

            return true;
        });

        (string) $p;

        \restore_error_handler();

        self::assertCount(1, $errors);
        self::assertSame(\E_USER_ERROR, $errors[0]['number']);
        self::assertStringStartsWith('Viserio\Component\Http\Stream\PumpStream::__toString exception:', $errors[0]['message']);
    }
}
