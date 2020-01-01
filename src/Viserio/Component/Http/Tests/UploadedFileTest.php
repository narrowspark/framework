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

namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\UploadedFile;
use Viserio\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Contract\Http\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 */
final class UploadedFileTest extends TestCase
{
    /** @var mixed[] */
    protected $cleanup;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->cleanup = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        foreach ($this->cleanup as $file) {
            if (\is_string($file) && \file_exists($file)) {
                \unlink($file);

                $this->cleanup = [];
            }
        }
    }

    /**
     * @return array<mixed>
     */
    public function provideRaisesExceptionOnInvalidStreamOrFileCases(): iterable
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'array' => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    /**
     * @dataProvider provideRaisesExceptionOnInvalidStreamOrFileCases
     *
     * @param mixed $streamOrFile
     */
    public function testRaisesExceptionOnInvalidStreamOrFile($streamOrFile): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream or file provided for UploadedFile');

        new UploadedFile($streamOrFile, 0, \UPLOAD_ERR_OK);
    }

    public function testRaisesExceptionOnInvalidError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid error status for UploadedFile');

        /** @var resource $stream */
        $stream = \fopen('php://temp', 'rb');

        $stream = new Stream($stream);
        new UploadedFile($stream, 0, 9999);
    }

    public function testGetStreamReturnsOriginalStreamObject(): void
    {
        /** @var resource $handler */
        $handler = \fopen('php://temp', 'rb');

        $stream = new Stream($handler);
        $upload = new UploadedFile($stream, 0, \UPLOAD_ERR_OK);

        self::assertSame($stream, $upload->getStream());
    }

    public function testGetStreamReturnsWrappedPhpStream(): void
    {
        /** @var resource $stream */
        $stream = \fopen('php://temp', 'w+b');

        $upload = new UploadedFile($stream, 0, \UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream()->detach();

        self::assertSame($stream, $uploadStream);
    }

    public function testGetStreamReturnsStreamForFile(): void
    {
        /** @var string $stream */
        $stream = \tempnam(\sys_get_temp_dir(), 'stream_file');

        $this->cleanup[] = $stream;

        $upload = new UploadedFile($stream, 0, \UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream();

        $r = new ReflectionProperty($uploadStream, 'filename');
        $r->setAccessible(true);

        self::assertSame($stream, $r->getValue($uploadStream));
    }

    public function testSuccessful(): void
    {
        $body = 'Foo bar!';

        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $stream = new Stream($handler);
        $upload = new UploadedFile($stream, $stream->getSize() ?? 0, \UPLOAD_ERR_OK, 'filename.txt', 'text/plain');

        self::assertEquals($stream->getSize(), $upload->getSize());
        self::assertEquals('filename.txt', $upload->getClientFilename());
        self::assertEquals('text/plain', $upload->getClientMediaType());

        /** @var string $to */
        $to = \tempnam(\sys_get_temp_dir(), 'successful');

        $this->cleanup[] = $to;

        $upload->moveTo($to);

        self::assertFileExists($to);
        self::assertStringEqualsFile($to, $stream->__toString());
    }

    /**
     * @return array<mixed>
     */
    public function provideMoveRaisesExceptionForInvalidPathCases(): iterable
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'empty' => [''],
            'array' => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    /**
     * @dataProvider provideMoveRaisesExceptionForInvalidPathCases
     *
     * @param mixed $path
     */
    public function testMoveRaisesExceptionForInvalidPath($path): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('path');

        $body = 'Foo bar!';

        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $stream = new Stream($handler);
        $upload = new UploadedFile($stream, 0, \UPLOAD_ERR_OK);

        $this->cleanup[] = $path;

        $upload->moveTo($path);
    }

    public function testMoveCannotBeCalledMoreThanOnce(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');

        $body = 'Foo bar!';

        /** @var resource $stream */
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, 0, \UPLOAD_ERR_OK);

        /** @var string $to */
        $to = \tempnam(\sys_get_temp_dir(), 'diac');

        $this->cleanup[] = $to;

        $upload->moveTo($to);

        self::assertFileExists($to);

        $upload->moveTo($to);
    }

    public function testCannotRetrieveStreamAfterMove(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');

        $body = 'Foo bar!';

        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        \fwrite($handler, $body);
        \fseek($handler, 0);

        $stream = new Stream($handler);
        $upload = new UploadedFile($stream, 0, \UPLOAD_ERR_OK);

        /** @var string $to */
        $to = \tempnam(\sys_get_temp_dir(), 'diac');

        $this->cleanup[] = $to;

        $upload->moveTo($to);

        self::assertFileExists($to);

        $upload->getStream();
    }

    /**
     * @return array<string, array<int, int>>
     */
    public function nonOkErrorStatus(): iterable
    {
        return [
            'UPLOAD_ERR_INI_SIZE' => [\UPLOAD_ERR_INI_SIZE],
            'UPLOAD_ERR_FORM_SIZE' => [\UPLOAD_ERR_FORM_SIZE],
            'UPLOAD_ERR_PARTIAL' => [\UPLOAD_ERR_PARTIAL],
            'UPLOAD_ERR_NO_FILE' => [\UPLOAD_ERR_NO_FILE],
            'UPLOAD_ERR_NO_TMP_DIR' => [\UPLOAD_ERR_NO_TMP_DIR],
            'UPLOAD_ERR_CANT_WRITE' => [\UPLOAD_ERR_CANT_WRITE],
            'UPLOAD_ERR_EXTENSION' => [\UPLOAD_ERR_EXTENSION],
        ];
    }

    /**
     * @dataProvider nonOkErrorStatus
     *
     * @param mixed $status
     */
    public function testConstructorDoesNotRaiseExceptionForInvalidStreamWhenErrorStatusPresent($status): void
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);

        self::assertSame($status, $uploadedFile->getError());
    }

    /**
     * @dataProvider nonOkErrorStatus
     *
     * @param mixed $status
     */
    public function testMoveToRaisesExceptionWhenErrorStatusPresent($status): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('upload error');

        $uploadedFile = new UploadedFile('not ok', 0, $status);

        $uploadedFile->moveTo(__DIR__ . \DIRECTORY_SEPARATOR . \uniqid('', true));
    }

    /**
     * @dataProvider nonOkErrorStatus
     *
     * @param mixed $status
     */
    public function testGetStreamRaisesExceptionWhenErrorStatusPresent($status): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('upload error');

        $uploadedFile = new UploadedFile('not ok', 0, $status);

        $uploadedFile->getStream();
    }

    public function testMoveToCreatesStreamIfOnlyAFilenameWasProvided(): void
    {
        /** @var string $from */
        $from = \tempnam(\sys_get_temp_dir(), 'copy_from');
        $this->cleanup[] = $from;

        /** @var string $to */
        $to = \tempnam(\sys_get_temp_dir(), 'copy_to');
        $this->cleanup[] = $to;

        \copy(__FILE__, $from);

        $uploadedFile = new UploadedFile($from, 100, \UPLOAD_ERR_OK, \basename($from), 'text/plain');
        $uploadedFile->moveTo($to);

        self::assertFileEquals(__FILE__, $to);
    }
}
