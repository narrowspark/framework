<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

use Psr\Http\Message\UploadedFileInterface;
use Viserio\HttpFactory\UploadedFileFactory;

class UploadedFileFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new UploadedFileFactory();
    }

    public function testCreateUploadedFileWithString()
    {
        $filename = tempnam(sys_get_temp_dir(), 'http-factory-test');
        $content = 'i made this!';
        $size = strlen($content);

        file_put_contents($filename, $content);

        $file = $this->factory->createUploadedFile($filename);

        $this->assertUploadedFile($file, $content, $size);

        unlink($filename);
    }

    public function testCreateUploadedFileWithClientFilenameAndMediaType()
    {
        $tmpfname = tempnam('/tmp', 'foo');
        $upload = fopen($tmpfname, 'w+');
        $content = 'this is your capitan speaking';
        $error = \UPLOAD_ERR_OK;
        $clientFilename = 'test.txt';
        $clientMediaType = 'text/plain';
        fwrite($upload, $content);
        $file = $this->factory->createUploadedFile(
            $tmpfname,
            strlen($content),
            $error,
            $clientFilename,
            $clientMediaType
        );

        $this->assertUploadedFile($file, $content, null, $error, $clientFilename, $clientMediaType);
    }

    public function testCreateUploadedFileWithError()
    {
        $upload = tmpfile();
        $error = \UPLOAD_ERR_NO_FILE;
        $file = $this->factory->createUploadedFile($upload, null, $error);

        // Cannot use assertUploadedFile() here because the error prevents
        // fetching the content stream.
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertSame($error, $file->getError());
    }

    private function assertUploadedFile(
        $file,
        $content,
        $size = null,
        $error = null,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertSame($content, (string) $file->getStream());
        $this->assertSame($size ?: strlen($content), $file->getSize());
        $this->assertSame($error ?: UPLOAD_ERR_OK, $file->getError());
        $this->assertSame($clientFilename, $file->getClientFilename());
        $this->assertSame($clientMediaType, $file->getClientMediaType());
    }
}
