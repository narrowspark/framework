<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use Nyholm\NSA;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream;

class PipStreamTest extends TestCase
{
    /**
     * @var resource pipe stream file handle
     */
    private $pipeFh;

    /**
     * @var Stream
     */
    private $pipeStream;

    public function tearDown(): void
    {
        if ($this->pipeFh != null) {
            stream_get_contents($this->pipeFh); // prevent broken pipe error message
        }
    }

    public function testIsPipe(): void
    {
        $this->openPipeStream();

        self::assertTrue(NSA::invokeMethod($this->pipeStream, 'isPipe'));

        $this->pipeStream->detach();

        self::assertFalse(NSA::invokeMethod($this->pipeStream, 'isPipe'));

        $fhFile     = fopen(__FILE__, 'r');
        $fileStream = new Stream($fhFile);

        self::assertFalse(NSA::invokeMethod($fileStream, 'isPipe'));
    }

    public function testIsPipeReadable(): void
    {
        $this->openPipeStream();

        self::assertTrue($this->pipeStream->isReadable());
    }

    public function testPipeIsNotSeekable(): void
    {
        $this->openPipeStream();

        self::assertFalse($this->pipeStream->isSeekable());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCannotSeekPipe(): void
    {
        $this->openPipeStream();

        $this->pipeStream->seek(0);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCannotTellPipe(): void
    {
        $this->openPipeStream();

        $this->pipeStream->tell();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCannotRewindPipe(): void
    {
        $this->openPipeStream();

        $this->pipeStream->rewind();
    }

    public function testPipeGetSizeYieldsNull(): void
    {
        $this->openPipeStream();

        self::assertNull($this->pipeStream->getSize());
    }

    public function testClosePipe(): void
    {
        $this->openPipeStream();

        stream_get_contents($this->pipeFh); // prevent broken pipe error message

        $this->pipeStream->close();
        $this->pipeFh = null;

        self::assertFalse(NSA::invokeMethod($this->pipeStream, 'isPipe'));
    }

    public function testPipeToString(): void
    {
        $this->openPipeStream();

        self::assertSame('12', trim((string) $this->pipeStream));
    }

    public function testPipeGetContents(): void
    {
        $this->openPipeStream();

        $contents = trim($this->pipeStream->getContents());

        self::assertSame('12', $contents);
    }

    /**
     * Opens the pipe stream.
     */
    private function openPipeStream(): void
    {
        $this->pipeFh     = popen('echo 12', 'r');
        $this->pipeStream = new Stream($this->pipeFh);
    }
}
