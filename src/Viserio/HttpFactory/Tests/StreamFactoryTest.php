<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

use Viserio\Http\Stream;
use ArrayIterator;
use Viserio\HttpFactory\StreamFactory;
use Psr\Http\Message\StreamInterface;

class StreamFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new StreamFactory();
    }

    public function testCreateStream()
    {
        $resource = tmpfile();
        $stream = $this->factory->createStream($resource);
        $this->assertStream($stream, '');
    }

    private function assertStream($stream, $content)
    {
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame($content, (string) $stream);
    }

    public function testKeepsPositionOfResource()
    {
        $resource = fopen(__FILE__, 'r');
        fseek($resource, 10);

        $stream = $this->factory->createStream($resource);

        $this->assertEquals(10, $stream->tell());

        $stream->close();
    }

    public function testCreatesWithFactory()
    {
        $stream = $this->factory->createStream('foo');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertEquals('foo', $stream->getContents());

        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString()
    {
        $this->assertInstanceOf(Stream::class, $this->factory->createStream(''));
    }

    public function testFactoryCreatesFromResource()
    {
        $resource = fopen(__FILE__, 'r');
        $stream = $this->factory->createStream($resource);

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame(file_get_contents(__FILE__), (string) $stream);
    }
}
