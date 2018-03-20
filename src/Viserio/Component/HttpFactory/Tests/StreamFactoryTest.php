<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\HttpFactory\StreamFactory;

class StreamFactoryTest extends TestCase
{
    /**
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->factory = new StreamFactory();
    }

    public function testCreateStream(): void
    {
        $string = 'would you like some crumpets?';
        $stream = $this->factory->createStream($string);
        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromFile(): void
    {
        $string   = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();
        \file_put_contents($filename, $string);
        $stream = $this->factory->createStreamFromFile($filename);
        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromResource(): void
    {
        $string   = 'would you like some crumpets?';
        $resource = $this->createTemporaryResource($string);
        $stream   = $this->factory->createStreamFromResource($resource);
        $this->assertStream($stream, $string);
    }

    protected function assertStream($stream, $content): void
    {
        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame($content, (string) $stream);
    }

    protected function createTemporaryFile()
    {
        return \tempnam(\sys_get_temp_dir(), \uniqid('', true));
    }

    protected function createTemporaryResource($content = null)
    {
        $file     = $this->createTemporaryFile();
        $resource = \fopen($file, 'rb+');
        if ($content) {
            \fwrite($resource, $content);
            \rewind($resource);
        }

        return $resource;
    }
}
