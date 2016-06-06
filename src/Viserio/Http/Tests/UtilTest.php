<?php
namespace Viserio\Http\Tests;

use ArrayIterator;
use Viserio\Http\Stream\FnStream;
use Viserio\Http\Stream\PumpStream;
use Viserio\Http\Tests\Fixture\HasToString;
use Viserio\Http\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testCopiesToString()
    {
        $s = Util::getStream('foobaz');
        $this->assertEquals('foobaz', Util::copyToString($s));
        $s->seek(0);

        $this->assertEquals('foo', Util::copyToString($s, 3));
        $this->assertEquals('baz', Util::copyToString($s, 3));
        $this->assertEquals('', Util::copyToString($s));
    }

    public function testCopiesToStringStopsWhenReadFails()
    {
        $s1 = Util::getStream('foobaz');
        $s1 = FnStream::decorate($s1, [
            'read' => function () {
                return '';
            },
        ]);
        $result = Util::copyToString($s1);

        $this->assertEquals('', $result);
    }

    public function testCopiesToStream()
    {
        $s1 = Util::getStream('foobaz');
        $s2 = Util::getStream('');
        Util::copyToStream($s1, $s2);
        $this->assertEquals('foobaz', (string) $s2);

        $s2 = Util::getStream('');
        $s1->seek(0);

        Util::copyToStream($s1, $s2, 3);
        $this->assertEquals('foo', (string) $s2);

        Util::copyToStream($s1, $s2, 3);
        $this->assertEquals('foobaz', (string) $s2);
    }

    public function testStopsCopyToStreamWhenWriteFails()
    {
        $s1 = Util::getStream('foobaz');
        $s2 = Util::getStream('');
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);
        Util::copyToStream($s1, $s2);

        $this->assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenWriteFailsWithMaxLen()
    {
        $s1 = Util::getStream('foobaz');
        $s2 = Util::getStream('');
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);

        Util::copyToStream($s1, $s2, 10);
        $this->assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenReadFailsWithMaxLen()
    {
        $s1 = Util::getStream('foobaz');
        $s1 = FnStream::decorate($s1, ['read' => function () {
            return '';
        }]);
        $s2 = Util::getStream('');

        Util::copyToStream($s1, $s2, 10);
        $this->assertEquals('', (string) $s2);
    }

    public function testOpensFilesSuccessfully()
    {
        $r = Util::tryFopen(__FILE__, 'r');
        $this->assertInternalType('resource', $r);
        fclose($r);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to open /path/to/does/not/exist using mode r
     */
    public function testThrowsExceptionNotWarning()
    {
        Util::tryFopen('/path/to/does/not/exist', 'r');
    }

    public function testKeepsPositionOfResource()
    {
        $h = fopen(__FILE__, 'r');
        fseek($h, 10);
        $stream = Util::getStream($h);
        $this->assertEquals(10, $stream->tell());
        $stream->close();
    }

    public function testCreatesWithFactory()
    {
        $stream = Util::getStream('foo');
        $this->assertInstanceOf('Viserio\Http\Stream', $stream);
        $this->assertEquals('foo', $stream->getContents());
        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString()
    {
        $s = Util::getStream();
        $this->assertInstanceOf('Viserio\Http\Stream', $s);
    }

    public function testFactoryCreatesFromNull()
    {
        $s = Util::getStream(null);
        $this->assertInstanceOf('Viserio\Http\Stream', $s);
    }

    public function testFactoryCreatesFromResource()
    {
        $r = fopen(__FILE__, 'r');
        $s = Util::getStream($r);
        $this->assertInstanceOf('Viserio\Http\Stream', $s);
        $this->assertSame(file_get_contents(__FILE__), (string) $s);
    }

    public function testFactoryCreatesFromObjectWithToString()
    {
        $r = new HasToString();
        $s = Util::getStream($r);
        $this->assertInstanceOf('Viserio\Http\Stream', $s);
        $this->assertEquals('foo', (string) $s);
    }

    public function testCreatePassesThrough()
    {
        $s = Util::getStream('foo');
        $this->assertSame($s, Util::getStream($s));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionForUnknown()
    {
        Util::getStream(new \stdClass());
    }

    public function testReturnsCustomMetadata()
    {
        $s = Util::getStream('foo', ['metadata' => ['hwm' => 3]]);
        $this->assertEquals(3, $s->getMetadata('hwm'));
        $this->assertArrayHasKey('hwm', $s->getMetadata());
    }

    public function testCanSetSize()
    {
        $s = Util::getStream('', ['size' => 10]);
        $this->assertEquals(10, $s->getSize());
    }

    public function testCanCreateIteratorBasedStream()
    {
        $a = new ArrayIterator(['foo', 'bar', '123']);
        $p = Util::getStream($a);
        $this->assertInstanceOf(PumpStream::class, $p);
        $this->assertEquals('foo', $p->read(3));
        $this->assertFalse($p->eof());
        $this->assertEquals('b', $p->read(1));
        $this->assertEquals('a', $p->read(1));
        $this->assertEquals('r12', $p->read(3));
        $this->assertFalse($p->eof());
        $this->assertEquals('3', $p->getContents());
        $this->assertTrue($p->eof());
        $this->assertEquals(9, $p->tell());
    }
}
