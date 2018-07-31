<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\HttpFactory\UploadedFileFactory;

/**
 * @internal
 */
final class UploadedFileFactoryTest extends TestCase
{
    /**
     * @var string
     */
    private $fname;

    /**
     * @var \Psr\Http\Message\UploadedFileFactoryInterface
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setup(): void
    {
        \mkdir(__DIR__ . '/tmp');

        $this->factory = new UploadedFileFactory();

        $this->fname = \tempnam(__DIR__ . '/tmp', 'tfile');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
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
        static::assertInstanceOf(UploadedFileInterface::class, $file);
        static::assertSame($error, $file->getError());
    }

    /**
     * @param UploadedFileInterface $file
     * @param string                $content
     * @param null|int              $size
     * @param null|int              $error
     * @param null|string           $clientFilename
     * @param null|string           $clientMediaType
     */
    private function assertUploadedFile(
        UploadedFileInterface $file,
        string $content,
        int $size = null,
        int $error = null,
        string  $clientFilename = null,
        string $clientMediaType = null
    ): void {
        static::assertInstanceOf(UploadedFileInterface::class, $file);
        static::assertSame($content, (string) $file->getStream());
        static::assertSame($size ?: \mb_strlen($content), $file->getSize());
        static::assertSame($error ?: \UPLOAD_ERR_OK, $file->getError());
        static::assertSame($clientFilename, $file->getClientFilename());
        static::assertSame($clientMediaType, $file->getClientMediaType());
    }
}
