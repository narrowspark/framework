<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use ArrayIterator;
use Viserio\Http\Stream;
use Viserio\Http\StreamFactory;
use Viserio\Http\Stream\PumpStream;

class StreamFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testKeepsPositionOfResource()
    {
        $resource = fopen(__FILE__, 'r');
        fseek($resource, 10);

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromResource($resource);

        $this->assertEquals(10, $stream->tell());

        $stream->close();
    }

    public function testCreatesWithFactory()
    {
        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromString('foo');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertEquals('foo', $stream->getContents());

        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString()
    {
        $streamFactory = new StreamFactory();
        $this->assertInstanceOf(Stream::class, $streamFactory->createStream());
    }

    public function testFactoryCreatesFromResource()
    {
        $resource = fopen(__FILE__, 'r');
        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromResource($resource);

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame(file_get_contents(__FILE__), (string) $stream);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid resource type: string.
     */
    public function testFactoryCreatesFromResourceToThorwException()
    {
        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromResource('foo');
    }

    public function testCanCreateCallableBasedStream()
    {
        $resource = new ArrayIterator(['foo', 'bar', '123']);

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromCallback(function () use ($resource) {
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
