<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Util;
use Viserio\Http\Stream\NoSeekStream;

class NoSeekStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot seek a NoSeekStream
     */
    public function testCannotSeek()
    {
        $s = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->setMethods(['isSeekable', 'seek'])
            ->getMockForAbstractClass();

        $s->expects($this->never())->method('seek');
        $s->expects($this->never())->method('isSeekable');

        $wrapped = new NoSeekStream($s);
        $this->assertFalse($wrapped->isSeekable());
        $wrapped->seek(2);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot write to a non-writable stream
     */
    public function testHandlesClose()
    {
        $s = Util::getStream('foo');
        $wrapped = new NoSeekStream($s);
        $wrapped->close();
        $wrapped->write('foo');
    }
}
