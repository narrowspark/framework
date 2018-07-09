<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viserio\Component\Http\Stream\LimitStream;
use Viserio\Component\Http\Stream\PumpStream;

/**
 * @internal
 */
final class PumpStreamTest extends TestCase
{
    public function testHasMetadataAndSize(): void
    {
        $pump = new PumpStream(function (): void {
        }, [
            'metadata' => ['foo' => 'bar'],
            'size'     => 100,
        ]);

        static::assertEquals('bar', $pump->getMetadata('foo'));
        static::assertEquals(['foo' => 'bar'], $pump->getMetadata());
        static::assertEquals(100, $pump->getSize());
    }

    public function testCanReadFromCallable(): void
    {
        $pump = new PumpStream(function ($size) {
            return 'a';
        });

        static::assertEquals('a', $pump->read(1));
        static::assertEquals(1, $pump->tell());
        static::assertEquals('aaaaa', $pump->read(5));
        static::assertEquals(6, $pump->tell());
    }

    public function testStoresExcessDataInBuffer(): void
    {
        $called = [];

        $pump = new PumpStream(function ($size) use (&$called) {
            $called[] = $size;

            return 'abcdef';
        });

        static::assertEquals('a', $pump->read(1));
        static::assertEquals('b', $pump->read(1));
        static::assertEquals('cdef', $pump->read(4));
        static::assertEquals('abcdefabc', $pump->read(9));
        static::assertEquals([1, 9, 3], $called);
    }

    public function testInifiniteStreamWrappedInLimitStream(): void
    {
        $pump = new PumpStream(function () {
            return 'a';
        });
        $s = new LimitStream($pump, 5);

        static::assertEquals('aaaaa', (string) $s);
    }

    public function testDescribesCapabilities(): void
    {
        $pump = new PumpStream(function (): void {
        });

        static::assertTrue($pump->isReadable());
        static::assertFalse($pump->isSeekable());
        static::assertFalse($pump->isWritable());
        static::assertNull($pump->getSize());
        static::assertEquals('', $pump->getContents());
        static::assertEquals('', (string) $pump);

        $pump->close();

        static::assertEquals('', $pump->read(10));
        static::assertTrue($pump->eof());

        try {
            $pump->write('aa');
            static::fail();
        } catch (RuntimeException $e) {
        }
    }

    public function testCanCreateCallableBasedStream(): void
    {
        $resource = new ArrayIterator(['foo', 'bar', '123']);

        $stream = new PumpStream(function () use ($resource) {
            if (! $resource->valid()) {
                return false;
            }

            $result = $resource->current();
            $resource->next();

            return $result;
        });

        static::assertInstanceOf(PumpStream::class, $stream);
        static::assertEquals('foo', $stream->read(3));
        static::assertFalse($stream->eof());
        static::assertEquals('b', $stream->read(1));
        static::assertEquals('a', $stream->read(1));
        static::assertEquals('r12', $stream->read(3));
        static::assertFalse($stream->eof());
        static::assertEquals('3', $stream->getContents());
        static::assertTrue($stream->eof());
        static::assertEquals(9, $stream->tell());
    }
}
