<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\HttpFactory\StreamFactory;

class StreamFactoryTest extends TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new StreamFactory();
    }

    public function testCreateStream()
    {
        $string = 'would you like some crumpets?';
        $stream = $this->factory->createStream($string);
        self::assertStream($stream, $string);
    }

    public function testCreateStreamFromFile()
    {
        $string   = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();
        file_put_contents($filename, $string);
        $stream = $this->factory->createStreamFromFile($filename);
        self::assertStream($stream, $string);
    }

    public function testCreateStreamFromResource()
    {
        $string   = 'would you like some crumpets?';
        $resource = $this->createTemporaryResource($string);
        $stream   = $this->factory->createStreamFromResource($resource);
        self::assertStream($stream, $string);
    }

    protected function assertStream($stream, $content)
    {
        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame($content, (string) $stream);
    }

    protected function createTemporaryFile()
    {
        return tempnam(sys_get_temp_dir(), uniqid());
    }

    protected function createTemporaryResource($content = null)
    {
        $file     = $this->createTemporaryFile();
        $resource = fopen($file, 'r+');
        if ($content) {
            fwrite($resource, $content);
            rewind($resource);
        }

        return $resource;
    }
}
