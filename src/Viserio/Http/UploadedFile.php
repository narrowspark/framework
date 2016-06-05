<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Viserio\Http\Stream\LazyOpenStream;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var int[]
     */
    private static $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    /**
     * The client-provided full path to the file
     *
     * @note this is public to maintain BC with 3.1.0 and earlier.
     *
     * @var string
     */
    public $file;

    /**
     * The client-provided file name.
     *
     * @var string
     */
    protected $name;

    /**
     * The client-provided media type of the file.
     *
     * @var string
     */
    protected $type;

    /**
     * The size of the file in bytes.
     *
     * @var int
     */
    protected $size;

    /**
     * A valid PHP UPLOAD_ERR_xxx code for the file upload.
     *
     * @var int
     */
    protected $error;

    /**
     * An optional StreamInterface wrapping the file resource.
     *
     * @var StreamInterface
     */
    protected $stream;

    /**
     * Indicates if the uploaded file has already been moved.
     *
     * @var bool
     */
    protected $moved = false;

    /**
     * @param StreamInterface|string|resource $streamOrFile
     * @param int                             $size
     * @param int                             $errorStatus
     * @param string|null                     $clientFilename
     * @param string|null                     $clientMediaType
     */
    public function __construct(
        $streamOrFile,
        int $size,
        int $errorStatus,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        $this->setError($errorStatus);
        $this->setSize($size);
        $this->setClientFilename($clientFilename);
        $this->setClientMediaType($clientMediaType);

        if ($this->isOk()) {
            $this->setStreamOrFile($streamOrFile);
        }
    }

    /**
     * @return bool
     */
    public function isMoved()
    {
        return $this->moved;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException if the upload was not successful.
     */
    public function getStream()
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        return new LazyOpenStream($this->file, 'r+');
    }

    /**
     * Depending on the value set file or stream variable
     *
     * @param mixed $streamOrFile
     *
     * @throws InvalidArgumentException
     */
    private function setStreamOrFile($streamOrFile)
    {
        if (is_string($streamOrFile)) {
            $this->file = $streamOrFile;
        } elseif (is_resource($streamOrFile)) {
            $this->stream = new Stream($streamOrFile);
        } elseif ($streamOrFile instanceof StreamInterface) {
            $this->stream = $streamOrFile;
        } else {
            throw new InvalidArgumentException(
                'Invalid stream or file provided for UploadedFile'
            );
        }
    }

    /**
     * @param int $error
     *
     * @throws InvalidArgumentException
     */
    private function setError($error)
    {
        if (false === is_int($error)) {
            throw new InvalidArgumentException(
                'Upload file error status must be an integer'
            );
        }

        if (false === in_array($error, UploadedFile::$errors)) {
            throw new InvalidArgumentException(
                'Invalid error status for UploadedFile'
            );
        }

        $this->error = $error;
    }

    /**
     * @param int $size
     */
    private function setSize(int $size)
    {
        $this->size = $size;
    }

    /**
     * @param mixed $param
     *
     * @return bool
     */
    private function isStringOrNull($param): bool
    {
        return in_array(gettype($param), ['string', 'NULL']);
    }

    /**
     * @param mixed $param
     *
     * @return bool
     */
    private function isStringNotEmpty($param): bool
    {
        return is_string($param) &&  empty($param) === false;
    }

    /**
     * @param string|null $clientFilename
     *
     * @throws InvalidArgumentException
     */
    private function setClientFilename($clientFilename)
    {
        if ($this->isStringOrNull($clientFilename) === false) {
            throw new InvalidArgumentException(
                'Upload file client filename must be a string or null'
            );
        }

        $this->clientFilename = $clientFilename;
    }

    /**
     * @param string|null $clientMediaType
     *
     * @throws InvalidArgumentException
     */
    private function setClientMediaType($clientMediaType)
    {
        if ($this->isStringOrNull($clientMediaType) === false) {
            throw new InvalidArgumentException(
                'Upload file client media type must be a string or null'
            );
        }

        $this->clientMediaType = $clientMediaType;
    }

    /**
     * Return true if there is no upload error
     *
     * @return bool
     */
    private function isOk()
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @throws RuntimeException if is moved or not ok
     */
    private function validateActive()
    {
        if (false === $this->isOk()) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }
}
