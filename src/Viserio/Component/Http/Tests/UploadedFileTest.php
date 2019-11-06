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
use const DIRECTORY_SEPARATOR;
use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

/**
 * @internal
 *
 * @small
 */
final class UploadedFileTest extends TestCase
{
    protected $cleanup;

    protected function setUp(): void
    {
        $this->cleanup = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $file) {
            if (\is_string($file) && \is_scalar($file) && \file_exists($file)) {
                \unlink($file);
                $this->cleanup = [];
            }
        }
    }

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

        new UploadedFile($streamOrFile, 0, UPLOAD_ERR_OK);
    }

    public function testRaisesExceptionOnInvalidError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid error status for UploadedFile');

        $stream = new Stream(\fopen('php://temp', 'rb'));
        new UploadedFile($stream, 0, 9999);
    }

    public function testGetStreamReturnsOriginalStreamObject(): void
    {
        $stream = new Stream(\fopen('php://temp', 'rb'));
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        self::assertSame($stream, $upload->getStream());
    }

    public function testGetStreamReturnsWrappedPhpStream(): void
    {
        $stream = \fopen('php://temp', 'w+b');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream()->detach();

        self::assertSame($stream, $uploadStream);
    }

    public function testGetStreamReturnsStreamForFile(): void
    {
        $this->cleanup[] = $stream = \tempnam(\sys_get_temp_dir(), 'stream_file');

        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream();

        $r = new ReflectionProperty($uploadStream, 'filename');
        $r->setAccessible(true);

        self::assertSame($stream, $r->getValue($uploadStream));
    }

    public function testSuccessful(): void
    {
        $body = 'Foo bar!';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK, 'filename.txt', 'text/plain');

        self::assertEquals($stream->getSize(), $upload->getSize());
        self::assertEquals('filename.txt', $upload->getClientFilename());
        self::assertEquals('text/plain', $upload->getClientMediaType());

        $this->cleanup[] = $to = \tempnam(\sys_get_temp_dir(), 'successful');

        $upload->moveTo($to);

        self::assertFileExists($to);
        self::assertStringEqualsFile($to, $stream->__toString());
    }

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
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $path;

        $upload->moveTo($path);
    }

    public function testMoveCannotBeCalledMoreThanOnce(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');

        $body = 'Foo bar!';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $to = \tempnam(\sys_get_temp_dir(), 'diac');

        $upload->moveTo($to);

        self::assertFileExists($to);

        $upload->moveTo($to);
    }

    public function testCannotRetrieveStreamAfterMove(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('moved');

        $body = 'Foo bar!';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $to = \tempnam(\sys_get_temp_dir(), 'diac');

        $upload->moveTo($to);

        self::assertFileExists($to);

        $upload->getStream();
    }

    public function nonOkErrorStatus(): iterable
    {
        return [
            'UPLOAD_ERR_INI_SIZE' => [UPLOAD_ERR_INI_SIZE],
            'UPLOAD_ERR_FORM_SIZE' => [UPLOAD_ERR_FORM_SIZE],
            'UPLOAD_ERR_PARTIAL' => [UPLOAD_ERR_PARTIAL],
            'UPLOAD_ERR_NO_FILE' => [UPLOAD_ERR_NO_FILE],
            'UPLOAD_ERR_NO_TMP_DIR' => [UPLOAD_ERR_NO_TMP_DIR],
            'UPLOAD_ERR_CANT_WRITE' => [UPLOAD_ERR_CANT_WRITE],
            'UPLOAD_ERR_EXTENSION' => [UPLOAD_ERR_EXTENSION],
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

        $uploadedFile->moveTo(__DIR__ . DIRECTORY_SEPARATOR . \uniqid('', true));
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
        $this->cleanup[] = $from = \tempnam(\sys_get_temp_dir(), 'copy_from');
        $this->cleanup[] = $to = \tempnam(\sys_get_temp_dir(), 'copy_to');

        \copy(__FILE__, $from);

        $uploadedFile = new UploadedFile($from, 100, UPLOAD_ERR_OK, \basename($from), 'text/plain');
        $uploadedFile->moveTo($to);

        self::assertFileEquals(__FILE__, $to);
    }
}
