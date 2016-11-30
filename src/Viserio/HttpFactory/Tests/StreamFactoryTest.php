<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

use Psr\Http\Message\StreamInterface;
use Viserio\Http\Stream;
use Viserio\HttpFactory\StreamFactory;

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
        $stream = $this->factory->createStreamFromResource($resource);
        self::assertStream($stream, '');
    }

    public function testKeepsPositionOfResource()
    {
        $resource = fopen(__FILE__, 'r');
        fseek($resource, 10);

        $stream = $this->factory->createStreamFromResource($resource);

        self::assertEquals(10, $stream->tell());

        $stream->close();
    }

    public function testCreatesWithFactory()
    {
        $stream = $this->factory->createStream('foo');

        self::assertInstanceOf(Stream::class, $stream);
        self::assertEquals('foo', $stream->getContents());

        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString()
    {
        self::assertInstanceOf(Stream::class, $this->factory->createStream(''));
    }

    public function testFactoryCreatesFromResource()
    {
        $resource = fopen(__FILE__, 'r');
        $stream = $this->factory->createStreamFromResource($resource);

        self::assertInstanceOf(Stream::class, $stream);
        self::assertSame(file_get_contents(__FILE__), (string) $stream);
    }

    private function assertStream($stream, $content)
    {
        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame($content, (string) $stream);
    }
}
