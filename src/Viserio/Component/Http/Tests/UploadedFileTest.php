<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\UploadedFile;

class UploadedFileTest extends TestCase
{
    protected $cleanup;

    public function setUp()
    {
        $this->cleanup = [];
    }

    public function tearDown()
    {
        foreach ($this->cleanup as $file) {
            if (is_string($file) && is_scalar($file) && file_exists($file)) {
                unlink($file);
                $this->cleanup = [];
            }
        }
    }

    public function invalidStreams()
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'array'  => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    /**
     * @dataProvider invalidStreams
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid stream or file provided for UploadedFile
     *
     * @param mixed $streamOrFile
     */
    public function testRaisesExceptionOnInvalidStreamOrFile($streamOrFile)
    {
        new UploadedFile($streamOrFile, 0, UPLOAD_ERR_OK);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid error status for UploadedFile
     */
    public function testRaisesExceptionOnInvalidError()
    {
        $stream = new Stream(fopen('php://temp', 'r'));
        new UploadedFile($stream, 0, 9999);
    }

    public function testGetStreamReturnsOriginalStreamObject()
    {
        $stream = new Stream(fopen('php://temp', 'r'));
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        self::assertSame($stream, $upload->getStream());
    }

    public function testGetStreamReturnsWrappedPhpStream()
    {
        $stream       = fopen('php://temp', 'wb+');
        $upload       = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream()->detach();

        self::assertSame($stream, $uploadStream);
    }

    public function testGetStreamReturnsStreamForFile()
    {
        $this->cleanup[] = $stream = tempnam(sys_get_temp_dir(), 'stream_file');

        $upload       = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream();

        $r = new ReflectionProperty($uploadStream, 'filename');
        $r->setAccessible(true);

        self::assertSame($stream, $r->getValue($uploadStream));
    }

    public function testSuccessful()
    {
        $body   = 'Foo bar!';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK, 'filename.txt', 'text/plain');

        self::assertEquals($stream->getSize(), $upload->getSize());
        self::assertEquals('filename.txt', $upload->getClientFilename());
        self::assertEquals('text/plain', $upload->getClientMediaType());
        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'successful');

        $upload->moveTo($to);

        self::assertFileExists($to);
        self::assertEquals($stream->__toString(), file_get_contents($to));
    }

    public function invalidMovePaths()
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'empty'  => [''],
            'array'  => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    /**
     * @dataProvider invalidMovePaths
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage path
     *
     * @param mixed $path
     */
    public function testMoveRaisesExceptionForInvalidPath($path)
    {
        $body   = 'Foo bar!';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $path;

        $upload->moveTo($path);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage moved
     */
    public function testMoveCannotBeCalledMoreThanOnce()
    {
        $body   = 'Foo bar!';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'diac');

        $upload->moveTo($to);

        self::assertTrue(file_exists($to));

        $upload->moveTo($to);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage moved
     */
    public function testCannotRetrieveStreamAfterMove()
    {
        $body   = 'Foo bar!';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $stream = new Stream($stream);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'diac');

        $upload->moveTo($to);

        self::assertFileExists($to);

        $upload->getStream();
    }

    public function nonOkErrorStatus()
    {
        return [
            'UPLOAD_ERR_INI_SIZE'   => [UPLOAD_ERR_INI_SIZE],
            'UPLOAD_ERR_FORM_SIZE'  => [UPLOAD_ERR_FORM_SIZE],
            'UPLOAD_ERR_PARTIAL'    => [UPLOAD_ERR_PARTIAL],
            'UPLOAD_ERR_NO_FILE'    => [UPLOAD_ERR_NO_FILE],
            'UPLOAD_ERR_NO_TMP_DIR' => [UPLOAD_ERR_NO_TMP_DIR],
            'UPLOAD_ERR_CANT_WRITE' => [UPLOAD_ERR_CANT_WRITE],
            'UPLOAD_ERR_EXTENSION'  => [UPLOAD_ERR_EXTENSION],
        ];
    }

    /**
     * @dataProvider nonOkErrorStatus
     *
     * @param mixed $status
     */
    public function testConstructorDoesNotRaiseExceptionForInvalidStreamWhenErrorStatusPresent($status)
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);
        self::assertSame($status, $uploadedFile->getError());
    }

    /**
     * @dataProvider nonOkErrorStatus
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage upload error
     *
     * @param mixed $status
     */
    public function testMoveToRaisesExceptionWhenErrorStatusPresent($status)
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);

        $uploadedFile->moveTo(__DIR__ . '/' . uniqid());
    }

    /**
     * @dataProvider nonOkErrorStatus
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage upload error
     *
     * @param mixed $status
     */
    public function testGetStreamRaisesExceptionWhenErrorStatusPresent($status)
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);

        $stream = $uploadedFile->getStream();
    }

    public function testMoveToCreatesStreamIfOnlyAFilenameWasProvided()
    {
        $this->cleanup[] = $from = tempnam(sys_get_temp_dir(), 'copy_from');
        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'copy_to');
        copy(__FILE__, $from);
        $uploadedFile = new UploadedFile($from, 100, UPLOAD_ERR_OK, basename($from), 'text/plain');
        $uploadedFile->moveTo($to);

        self::assertFileEquals(__FILE__, $to);
    }
}
