<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Component\HttpFactory\StreamFactory;

class StreamFactoryTest extends TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new StreamFactory();
    }

    protected function assertStream($stream, $content)
    {
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame($content, (string) $stream);
    }

    public function testCreateStream()
    {
        $string = 'would you like some crumpets?';
        $stream = $this->factory->createStream($string);
        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromFile()
    {
        $string = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();
        file_put_contents($filename, $string);
        $stream = $this->factory->createStreamFromFile($filename);
        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromResource()
    {
        $string = 'would you like some crumpets?';
        $resource = $this->createTemporaryResource($string);
        $stream = $this->factory->createStreamFromResource($resource);
        $this->assertStream($stream, $string);
    }

    protected function createTemporaryFile()
    {
        return tempnam(sys_get_temp_dir(), uniqid());
    }

    protected function createTemporaryResource($content = null)
    {
        $file = $this->createTemporaryFile();
        $resource = fopen($file, 'r+');
        if ($content) {
            fwrite($resource, $content);
            rewind($resource);
        }
        return $resource;
    }
}
