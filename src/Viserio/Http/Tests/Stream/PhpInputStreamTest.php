<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Stream\PhpInputStream;

class PhpInputStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var PhpInputStream
     */
    protected $stream;

    public function setUp()
    {
        $this->file = dirname(__DIR__) . '/Fixture/php-input-stream.txt';
        $this->stream = new PhpInputStream($this->file);
    }

    public function getFileContents()
    {
        return file_get_contents($this->file);
    }

    public function assertStreamContents($test, $message = null)
    {
        $content = $this->getFileContents();
        $this->assertEquals($content, $test, $message);
    }

    public function testStreamIsNeverWritable()
    {
        $this->assertFalse($this->stream->isWritable());
    }

    public function testCanReadStreamIteratively()
    {
        $body = '';

        while (! $this->stream->eof()) {
            $body .= $this->stream->read(128);
        }

        $this->assertStreamContents($body);
    }

    public function testGetContentsReturnsRemainingContentsOfStream()
    {
        $start = $this->stream->read(128);
        $remainder = $this->stream->getContents();
        $contents = $this->getFileContents();
        $this->assertEquals(substr($contents, 128), $remainder);
    }

    public function testCastingToStringReturnsFullContentsRegardlesOfPriorReads()
    {
        $start = $this->stream->read(128);
        $this->assertStreamContents($this->stream->__toString());
    }

    public function testMultipleCastsToStringReturnSameContentsEvenIfReadsOccur()
    {
        $first  = (string) $this->stream;
        $read   = $this->stream->read(128);
        $second = (string) $this->stream;

        $this->assertSame($first, $second);
    }
}
