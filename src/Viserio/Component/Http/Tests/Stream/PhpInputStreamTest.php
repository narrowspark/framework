<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\PhpInputStream;

/**
 * @internal
 *
 * @small
 */
final class PhpInputStreamTest extends TestCase
{
    /** @var string */
    protected $file;

    /** @var PhpInputStream */
    protected $stream;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->file = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'php-input-stream.txt';
        $this->stream = new PhpInputStream($this->file);
    }

    /**
     * @return string
     */
    public function getFileContents(): string
    {
        return (string) \file_get_contents($this->file);
    }

    /**
     * @param string $test
     * @param string $message
     *
     * @return void
     */
    public function assertStreamContents(string $test, string $message = ''): void
    {
        $content = $this->getFileContents();

        self::assertEquals($content, $test, $message);
    }

    public function testStreamIsNeverWritable(): void
    {
        self::assertFalse($this->stream->isWritable());
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
        $contents = $this->getFileContents();

        self::assertEquals(\substr($contents, 128), $remainder);
    }

    public function testGetContentsReturnCacheWhenReachedEof(): void
    {
        $this->stream->getContents();

        $this->assertStreamContents($this->stream->getContents());

        $stream = new PhpInputStream('data://,0');
        $stream->read(1);
        $stream->read(1);

        self::assertSame('0', $stream->getContents(), 'Don\'t evaluate 0 as empty');
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

        self::assertSame($first, $second);
    }
}
