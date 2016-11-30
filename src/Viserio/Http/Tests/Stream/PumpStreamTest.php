<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use ArrayIterator;
use RuntimeException;
use Viserio\Http\Stream\LimitStream;
use Viserio\Http\Stream\PumpStream;

class PumpStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testHasMetadataAndSize()
    {
        $pump = new PumpStream(function () {
        }, [
            'metadata' => ['foo' => 'bar'],
            'size' => 100,
        ]);

        self::assertEquals('bar', $pump->getMetadata('foo'));
        self::assertEquals(['foo' => 'bar'], $pump->getMetadata());
        self::assertEquals(100, $pump->getSize());
    }

    public function testCanReadFromCallable()
    {
        $pump = new PumpStream(function ($size) {
            return 'a';
        });

        self::assertEquals('a', $pump->read(1));
        self::assertEquals(1, $pump->tell());
        self::assertEquals('aaaaa', $pump->read(5));
        self::assertEquals(6, $pump->tell());
    }

    public function testStoresExcessDataInBuffer()
    {
        $called = [];

        $pump = new PumpStream(function ($size) use (&$called) {
            $called[] = $size;

            return 'abcdef';
        });

        self::assertEquals('a', $pump->read(1));
        self::assertEquals('b', $pump->read(1));
        self::assertEquals('cdef', $pump->read(4));
        self::assertEquals('abcdefabc', $pump->read(9));
        self::assertEquals([1, 9, 3], $called);
    }

    public function testInifiniteStreamWrappedInLimitStream()
    {
        $pump = new PumpStream(function () {
            return 'a';
        });
        $s = new LimitStream($pump, 5);

        self::assertEquals('aaaaa', (string) $s);
    }

    public function testDescribesCapabilities()
    {
        $pump = new PumpStream(function () {
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
            self::assertFalse($pump->write('aa'));
            $this->fail();
        } catch (RuntimeException $e) {
        }
    }

    public function testCanCreateCallableBasedStream()
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

        self::assertInstanceOf(PumpStream::class, $stream);
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
}
