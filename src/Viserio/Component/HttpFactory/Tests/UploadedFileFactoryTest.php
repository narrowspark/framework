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

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UploadedFileFactory;

/**
 * @internal
 *
 * @small
 */
final class UploadedFileFactoryTest extends TestCase
{
    /** @var UploadedFileFactoryInterface */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new UploadedFileFactory();
    }

    public function testCreateUploadedFileWithClientFilenameAndMediaType(): void
    {
        $content = 'this is your capitan speaking';
        $upload = $this->createStream($content);
        $error = \UPLOAD_ERR_OK;
        $clientFilename = 'test.txt';
        $clientMediaType = 'text/plain';

        $file = $this->factory->createUploadedFile($upload, null, $error, $clientFilename, $clientMediaType);

        $this->assertUploadedFile($file, $content, null, $error, $clientFilename, $clientMediaType);
    }

    public function testCreateUploadedFileWithError(): void
    {
        $upload = $this->createStream('foobar');
        $error = \UPLOAD_ERR_NO_FILE;

        $file = $this->factory->createUploadedFile($upload, null, $error);

        // Cannot use assertUploadedFile() here because the error prevents
        // fetching the content stream.
        self::assertInstanceOf(UploadedFileInterface::class, $file);
        self::assertSame($error, $file->getError());
    }

    /**
     * @param mixed $content
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream($content): StreamInterface
    {
        return (new StreamFactory())->createStream($content);
    }

    protected function assertUploadedFile(
        $file,
        $content,
        $size = null,
        $error = null,
        $clientFilename = null,
        $clientMediaType = null
    ): void {
        self::assertInstanceOf(UploadedFileInterface::class, $file);
        self::assertSame($content, (string) $file->getStream());
        self::assertSame($size ?: \strlen($content), $file->getSize());
        self::assertSame($error ?: \UPLOAD_ERR_OK, $file->getError());
        self::assertSame($clientFilename, $file->getClientFilename());
        self::assertSame($clientMediaType, $file->getClientMediaType());
    }
}
