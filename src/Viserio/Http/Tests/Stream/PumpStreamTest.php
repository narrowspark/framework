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

        $this->assertEquals('bar', $pump->getMetadata('foo'));
        $this->assertEquals(['foo' => 'bar'], $pump->getMetadata());
        $this->assertEquals(100, $pump->getSize());
    }

    public function testCanReadFromCallable()
    {
        $pump = new PumpStream(function ($size) {
            return 'a';
        });

        $this->assertEquals('a', $pump->read(1));
        $this->assertEquals(1, $pump->tell());
        $this->assertEquals('aaaaa', $pump->read(5));
        $this->assertEquals(6, $pump->tell());
    }

    public function testStoresExcessDataInBuffer()
    {
        $called = [];

        $pump = new PumpStream(function ($size) use (&$called) {
            $called[] = $size;

            return 'abcdef';
        });

        $this->assertEquals('a', $pump->read(1));
        $this->assertEquals('b', $pump->read(1));
        $this->assertEquals('cdef', $pump->read(4));
        $this->assertEquals('abcdefabc', $pump->read(9));
        $this->assertEquals([1, 9, 3], $called);
    }

    public function testInifiniteStreamWrappedInLimitStream()
    {
        $pump = new PumpStream(function () {
            return 'a';
        });
        $s = new LimitStream($pump, 5);

        $this->assertEquals('aaaaa', (string) $s);
    }

    public function testDescribesCapabilities()
    {
        $pump = new PumpStream(function () {
        });

        $this->assertTrue($pump->isReadable());
        $this->assertFalse($pump->isSeekable());
        $this->assertFalse($pump->isWritable());
        $this->assertNull($pump->getSize());
        $this->assertEquals('', $pump->getContents());
        $this->assertEquals('', (string) $pump);

        $pump->close();

        $this->assertEquals('', $pump->read(10));
        $this->assertTrue($pump->eof());

        try {
            $this->assertFalse($pump->write('aa'));
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

        $this->assertInstanceOf(PumpStream::class, $stream);
        $this->assertEquals('foo', $stream->read(3));
        $this->assertFalse($stream->eof());
        $this->assertEquals('b', $stream->read(1));
        $this->assertEquals('a', $stream->read(1));
        $this->assertEquals('r12', $stream->read(3));
        $this->assertFalse($stream->eof());
        $this->assertEquals('3', $stream->getContents());
        $this->assertTrue($stream->eof());
        $this->assertEquals(9, $stream->tell());
    }
}
