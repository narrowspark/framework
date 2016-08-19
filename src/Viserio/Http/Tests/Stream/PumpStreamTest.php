<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use RuntimeException;
use Viserio\Http\Stream\LimitStream;
use Viserio\Http\Stream\PumpStream;
use Viserio\Http\StreamFactory;

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
        $pump =  (new StreamFactory)->createStreamFromCallback(function ($size) {
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

        $pump =  (new StreamFactory)->createStreamFromCallback(function ($size) use (&$called) {
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
        $pump =  (new StreamFactory)->createStreamFromCallback(function () {
            return 'a';
        });
        $s = new LimitStream($pump, 5);

        $this->assertEquals('aaaaa', (string) $s);
    }

    public function testDescribesCapabilities()
    {
        $pump =  (new StreamFactory)->createStreamFromCallback(function () {
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
}
