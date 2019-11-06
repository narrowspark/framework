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

namespace Viserio\Component\HttpFactory\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\Tests\Traits\StreamHelperTrait;

/**
 * @internal
 *
 * @small
 */
final class StreamFactoryTest extends TestCase
{
    use StreamHelperTrait;

    /** @var \Viserio\Component\HttpFactory\StreamFactory */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new StreamFactory();
    }

    public function testCreateStreamFromFileCursorPosition(): void
    {
        $string = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();

        \file_put_contents($filename, $string);

        $resource = \fopen($filename, 'r');
        $fopenTell = \ftell($resource);

        fclose($resource);

        $stream = $this->factory->createStreamFromFile($filename);

        self::assertSame($fopenTell, $stream->tell());
    }

    public function testCreateStreamFromResourceCursorPosition(): void
    {
        $string = 'would you like some crumpets?';
        $resource1 = $this->createTemporaryResource($string);

        \fseek($resource1, 0, \SEEK_SET);

        $stream1 = $this->factory->createStreamFromResource($resource1);
        self::assertSame(0, $stream1->tell());
        $resource2 = $this->createTemporaryResource($string);

        \fseek($resource2, 0, \SEEK_END);

        $stream2 = $this->factory->createStreamFromResource($resource2);
        self::assertSame(\strlen($string), $stream2->tell());
        $resource3 = $this->createTemporaryResource($string);

        \fseek($resource3, 15, \SEEK_SET);

        $stream3 = $this->factory->createStreamFromResource($resource3);

        self::assertSame(15, $stream3->tell());
    }

    public function testCreateStreamWithoutArgument(): void
    {
        $stream = $this->factory->createStream();

        $this->assertStream($stream, '');
    }

    public function testCreateStreamWithEmptyString(): void
    {
        $string = '';

        $stream = $this->factory->createStream($string);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamWithASCIIString(): void
    {
        $string = 'would you like some crumpets?';

        $stream = $this->factory->createStream($string);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamWithMultiByteMultiLineString(): void
    {
        $string = "would you\r\nlike some\n\u{1F950}?";

        $stream = $this->factory->createStream($string);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromFile(): void
    {
        $string = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();

        \file_put_contents($filename, $string);

        $stream = $this->factory->createStreamFromFile($filename);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromNonExistingFile(): void
    {
        $filename = $this->createTemporaryFile();
        \unlink($filename);

        $this->expectException(RuntimeException::class);

        $this->factory->createStreamFromFile($filename);
    }

    public function testCreateStreamFromInvalidFileName(): void
    {
        $this->expectException(RuntimeException::class);

        $this->factory->createStreamFromFile('');
    }

    public function testCreateStreamFromFileIsReadOnlyByDefault(): void
    {
        $string = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();

        $stream = $this->factory->createStreamFromFile($filename);

        $this->expectException(RuntimeException::class);
        $stream->write($string);
    }

    public function testCreateStreamFromFileWithWriteOnlyMode(): void
    {
        $filename = $this->createTemporaryFile();

        $stream = $this->factory->createStreamFromFile($filename, 'w');

        $this->expectException(RuntimeException::class);
        $stream->read(1);
    }

    public function testCreateStreamFromFileWithNoMode(): void
    {
        $filename = $this->createTemporaryFile();

        $this->expectException(Exception::class);

        $this->factory->createStreamFromFile($filename, '');
    }

    public function testCreateStreamFromFileWithInvalidMode(): void
    {
        $filename = $this->createTemporaryFile();

        $this->expectException(Exception::class);

        $this->factory->createStreamFromFile($filename, "\u{2620}");
    }

    public function testCreateStreamFromResource(): void
    {
        $string = 'would you like some crumpets?';
        $resource = $this->createTemporaryResource($string);

        $stream = $this->factory->createStreamFromResource($resource);

        $this->assertStream($stream, $string);
    }

    /**
     * @param \Psr\Http\Message\StreamInterface $stream
     * @param string                            $content
     */
    protected function assertStream(StreamInterface $stream, string $content): void
    {
        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame($content, (string) $stream);
    }
}
