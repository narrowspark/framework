<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\PhpInputStream;

/**
 * @internal
 */
final class PhpInputStreamTest extends TestCase
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var PhpInputStream
     */
    protected $stream;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->file   = \dirname(__DIR__) . '/Fixture/php-input-stream.txt';
        $this->stream = new PhpInputStream($this->file);
    }

    public function getFileContents()
    {
        return \file_get_contents($this->file);
    }

    public function assertStreamContents($test, string $message = ''): void
    {
        $content = $this->getFileContents();

        static::assertEquals($content, $test, $message);
    }

    public function testStreamIsNeverWritable(): void
    {
        static::assertFalse($this->stream->isWritable());
    }

    public function testCanReadStreamIteratively(): void
    {
        $body = '';

        while (! $this->stream->eof()) {
            $body .= $this->stream->read(128);
        }

        $this->assertStreamContents($body);
    }

    public function testGetContentsReturnsRemainingContentsOfStream(): void
    {
        $this->stream->read(128);
        $remainder = $this->stream->getContents();
        $contents  = $this->getFileContents();

        static::assertEquals(\mb_substr($contents, 128), $remainder);
    }

    public function testGetContentsReturnCacheWhenReachedEof(): void
    {
        $this->stream->getContents();

        $this->assertStreamContents($this->stream->getContents());

        $stream = new PhpInputStream('data://,0');
        $stream->read(1);
        $stream->read(1);

        static::assertSame('0', $stream->getContents(), 'Don\'t evaluate 0 as empty');
    }

    public function testCastingToStringReturnsFullContentsRegardlesOfPriorReads(): void
    {
        $this->stream->read(128);

        $this->assertStreamContents($this->stream->__toString());
    }

    public function testMultipleCastsToStringReturnSameContentsEvenIfReadsOccur(): void
    {
        $first = (string) $this->stream;
        $this->stream->read(128);
        $second = (string) $this->stream;

        static::assertSame($first, $second);
    }
}
