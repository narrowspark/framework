<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

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
}
