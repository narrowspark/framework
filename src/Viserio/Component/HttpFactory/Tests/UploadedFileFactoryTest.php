<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\HttpFactory\UploadedFileFactory;

class UploadedFileFactoryTest extends TestCase
{
    /**
     * @var string
     */
    private $fname;

    /**
     * @var \Interop\Http\Factory\UploadedFileFactoryInterface
     */
    private $factory;

    public function setup(): void
    {
        \mkdir(__DIR__ . '/tmp');

        $this->factory = new UploadedFileFactory();

        $this->fname = \tempnam(__DIR__ . '/tmp', 'tfile');
    }

    public function tearDown(): void
    {
        if (\file_exists($this->fname)) {
            \unlink($this->fname);
        }

        \rmdir(__DIR__ . '/tmp');

        parent::tearDown();
    }

    public function testCreateUploadedFileWithString(): void
    {
        $filename = \tempnam(\sys_get_temp_dir(), 'http-factory-test');
        $content  = 'i made this!';
        $size     = \mb_strlen($content);

        \file_put_contents($filename, $content);

        $file = $this->factory->createUploadedFile($filename);

        $this->assertUploadedFile($file, $content, $size);

        \unlink($filename);
    }

    public function testCreateUploadedFileWithClientFilenameAndMediaType(): void
    {
        $tmpfname        = $this->fname;
        $upload          = \fopen($tmpfname, 'wb+');
        $content         = 'this is your capitan speaking';
        $error           = \UPLOAD_ERR_OK;
        $clientFilename  = 'test.txt';
        $clientMediaType = 'text/plain';

        \fwrite($upload, $content);

        $file = $this->factory->createUploadedFile(
            $tmpfname,
            \mb_strlen($content),
            $error,
            $clientFilename,
            $clientMediaType
        );

        $this->assertUploadedFile($file, $content, null, $error, $clientFilename, $clientMediaType);
    }

    public function testCreateUploadedFileWithError(): void
    {
        $upload = \tmpfile();
        $error  = \UPLOAD_ERR_NO_FILE;
        $file   = $this->factory->createUploadedFile($upload, null, $error);

        // Cannot use assertUploadedFile() here because the error prevents
        // fetching the content stream.
        self::assertInstanceOf(UploadedFileInterface::class, $file);
        self::assertSame($error, $file->getError());
    }

    private function assertUploadedFile(
        $file,
        $content,
        $size = null,
        $error = null,
        $clientFilename = null,
        $clientMediaType = null
    ): void {
        self::assertInstanceOf(UploadedFileInterface::class, $file);
        self::assertSame($content, (string) $file->getStream());
        self::assertSame($size ?: \mb_strlen($content), $file->getSize());
        self::assertSame($error ?: UPLOAD_ERR_OK, $file->getError());
        self::assertSame($clientFilename, $file->getClientFilename());
        self::assertSame($clientMediaType, $file->getClientMediaType());
    }
}
