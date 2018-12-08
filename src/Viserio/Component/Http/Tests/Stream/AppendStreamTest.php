<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\RuntimeException;
use Viserio\Component\Http\Stream\AppendStream;
use Viserio\Component\Http\Util;

/**
 * @internal
 */
final class AppendStreamTest extends MockeryTestCase
{
    public function testValidatesStreamsAreReadable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each stream must be readable');

        $appendStream = new AppendStream();

        $s = $this->getMockBuilder(StreamInterface::class)
            ->setMethods(['isReadable'])
            ->getMockForAbstractClass();
        $s->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(false));

        $appendStream->addStream($s);
    }

    public function testValidatesSeekType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The AppendStream can only seek with SEEK_SET');

        $a = new AppendStream();
        $a->seek(100, \SEEK_CUR);
    }

    public function testTriesToRewindOnSeek(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to seek stream 0 of the AppendStream');

        $a = new AppendStream();
        $s = $this->getMockBuilder(StreamInterface::class)
            ->setMethods(['isReadable', 'rewind', 'isSeekable'])
            ->getMockForAbstractClass();
        $s->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));
        $s->expects($this->once())
            ->method('isSeekable')
            ->will($this->returnValue(true));
        $s->expects($this->once())
            ->method('rewind')
            ->will($this->throwException(new RuntimeException()));

        $a->addStream($s);
        $a->seek(10);
    }

    public function testSeeksToPositionByReading(): void
    {
        $a = new AppendStream([
            Util::createStreamFor('foo'),
            Util::createStreamFor('bar'),
            Util::createStreamFor('baz'),
        ]);

        $a->seek(3);
        $this->assertEquals(3, $a->tell());
        $this->assertEquals('bar', $a->read(3));

        $a->seek(6);
        $this->assertEquals(6, $a->tell());
        $this->assertEquals('baz', $a->read(3));
    }

    public function testDetachWithoutStreams(): void
    {
        $s = new AppendStream();
        $s->detach();

        $this->assertSame(0, $s->getSize());
        $this->assertTrue($s->eof());
        $this->assertTrue($s->isReadable());
        $this->assertSame('', (string) $s);
        $this->assertTrue($s->isSeekable());
        $this->assertFalse($s->isWritable());
    }

    public function testDetachesEachStream(): void
    {
        $handle = \fopen('php://temp', 'r');

        $s1 = Util::createStreamFor($handle);
        $s2 = Util::createStreamFor('bar');
        $a  = new AppendStream([$s1, $s2]);

        $a->detach();

        $this->assertSame(0, $a->getSize());
        $this->assertTrue($a->eof());
        $this->assertTrue($a->isReadable());
        $this->assertSame('', (string) $a);
        $this->assertTrue($a->isSeekable());
        $this->assertFalse($a->isWritable());

        $this->assertNull($s1->detach());
        $this->assertIsResource($handle, 'resource is not closed when detaching');

        fclose($handle);
    }

    public function testClosesEachStream(): void
    {
        $handle = \fopen('php://temp', 'r');

        $s1 = Util::createStreamFor($handle);
        $s2 = Util::createStreamFor('bar');
        $a  = new AppendStream([$s1, $s2]);

        $a->close();

        $this->assertSame(0, $a->getSize());
        $this->assertTrue($a->eof());
        $this->assertTrue($a->isReadable());
        $this->assertSame('', (string) $a);
        $this->assertTrue($a->isSeekable());
        $this->assertFalse($a->isWritable());

        $this->assertIsResource($handle);
    }

    public function testIsNotWritable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot write to an AppendStream');

        $a = new AppendStream([Util::createStreamFor('foo')]);

        $this->assertFalse($a->isWritable());
        $this->assertTrue($a->isSeekable());
        $this->assertTrue($a->isReadable());

        $a->write('foo');
    }

    public function testDoesNotNeedStreams(): void
    {
        $a = new AppendStream();

        $this->assertEquals('', (string) $a);
    }

    public function testCanReadFromMultipleStreams(): void
    {
        $a = new AppendStream([
            Util::createStreamFor('foo'),
            Util::createStreamFor('bar'),
            Util::createStreamFor('baz'),
        ]);

        $this->assertFalse($a->eof());
        $this->assertSame(0, $a->tell());
        $this->assertEquals('foo', $a->read(3));
        $this->assertEquals('bar', $a->read(3));
        $this->assertEquals('baz', $a->read(3));
        $this->assertSame('', $a->read(1));
        $this->assertTrue($a->eof());
        $this->assertSame(9, $a->tell());
        $this->assertEquals('foobarbaz', (string) $a);
    }

    public function testCanDetermineSizeFromMultipleStreams(): void
    {
        $a = new AppendStream([
            Util::createStreamFor('foo'),
            Util::createStreamFor('bar'),
        ]);

        $this->assertEquals(6, $a->getSize());

        $s = $this->getMockBuilder(StreamInterface::class)
            ->setMethods(['isSeekable', 'isReadable'])
            ->getMockForAbstractClass();
        $s->expects($this->once())
            ->method('isSeekable')
            ->will($this->returnValue(null));
        $s->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));
        $a->addStream($s);

        $this->assertNull($a->getSize());
    }

    public function testReturnsEmptyMetadata(): void
    {
        $s = new AppendStream();

        $this->assertEquals([], $s->getMetadata());
        $this->assertNull($s->getMetadata('foo'));
    }

    /**
     * Make sure expectException always exists, even on PHPUnit 4.
     *
     * @param string      $exception
     * @param null|string $message
     */
    public function expectException($exception, $message = null): void
    {
        if (\method_exists($this, 'setExpectedException')) {
            $this->expectException($exception);
            $this->expectExceptionMessage($message);
        } else {
            parent::expectException($exception);

            if (null !== $message) {
                $this->expectExceptionMessage($message);
            }
        }
    }
}
