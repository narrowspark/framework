<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\RuntimeException;
use Viserio\Component\Http\Stream\LazyOpenStream;

class UploadedFile implements UploadedFileInterface
{
    /**
     * All errors which can happen on a upload.
     *
     * @var int[]
     */
    protected const ERRORS = [
        \UPLOAD_ERR_OK,
        \UPLOAD_ERR_INI_SIZE,
        \UPLOAD_ERR_FORM_SIZE,
        \UPLOAD_ERR_PARTIAL,
        \UPLOAD_ERR_NO_FILE,
        \UPLOAD_ERR_NO_TMP_DIR,
        \UPLOAD_ERR_CANT_WRITE,
        \UPLOAD_ERR_EXTENSION,
    ];

    /**
     * The client-provided full path to the file.
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
     * Client media type of a file.
     *
     * @var null|string
     */
    protected $clientMediaType;

    /**
     * Client filename.
     *
     * @var null|string
     */
    protected $clientFilename;

    /**
     * Help textes for upload error.
     *
     * @var array
     */
    private static $errorMessages = [
        \UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        \UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        \UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        \UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        \UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        \UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        \UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];

    /**
     * Create a new uploaded file instance.
     *
     * @param resource|StreamInterface|string $streamOrFile
     * @param int                             $size
     * @param int                             $errorStatus
     * @param null|string                     $clientFilename
     * @param null|string                     $clientMediaType
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     */
    public function __construct(
        $streamOrFile,
        int $size,
        int $errorStatus         = \UPLOAD_ERR_OK,
        ?string $clientFilename  = null,
        ?string $clientMediaType = null
    ) {
        $this->setError($errorStatus);
        $this->size            = $size;
        $this->clientFilename  = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        if ($this->isOk()) {
            $this->setStreamOrFile($streamOrFile);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return null|int the file size in bytes or null if unknown
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     *
     * @return int one of PHP's UPLOAD_ERR_XXX constants
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Check if error is a int or a array, then set it.
     *
     * @param int $error
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     *
     * @return void
     */
    private function setError(int $error): void
    {
        if (! \in_array($error, self::ERRORS, true)) {
            throw new InvalidArgumentException('Invalid error status for UploadedFile.');
        }

        $this->error = $error;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Http\Exception\RuntimeException if the upload was not successful
     */
    public function getStream(): StreamInterface
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        return new LazyOpenStream($this->file, 'r+');
    }

    /**
     * {@inheritdoc}
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|string the filename sent by the client or null if none
     *                     was provided
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     *
     * @param string $targetPath path to which to move the uploaded file
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if the $path specified is invalid
     * @throws \Viserio\Component\Contract\Http\Exception\RuntimeException         if the upload was not successful or on any error during the move operation, or on
     *                                                                             the second or subsequent call to the method
     */
    public function moveTo($targetPath): void
    {
        $this->validateActive();

        if ($this->isStringNotEmpty($targetPath) === false) {
            throw new InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string.'
            );
        }

        if ($this->file) {
            ($this->moved = \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true))
                ? \rename($this->file, $targetPath)
                : \move_uploaded_file($this->file, $targetPath);
        } else {
            Util::copyToStream(
                $this->getStream(),
                new LazyOpenStream($targetPath, 'w')
            );

            $this->moved = true;
        }

        if ($this->moved === false) {
            throw new RuntimeException(
                \sprintf('Uploaded file could not be moved to %s', $targetPath)
            );
        }
    }

    /**
     * Set the fill the right variable.
     *
     * @param mixed $streamOrFile
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     *
     * @return void
     */
    private function setStreamOrFile($streamOrFile): void
    {
        if (\is_string($streamOrFile)) {
            $this->file = $streamOrFile;

            return;
        }

        if (\is_resource($streamOrFile)) {
            $this->stream = new Stream($streamOrFile);

            return;
        }

        if ($streamOrFile instanceof StreamInterface) {
            $this->stream = $streamOrFile;

            return;
        }

        throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile.');
    }

    /**
     * Check if is a string or is empty.
     *
     * @param mixed $param
     *
     * @return bool
     */
    private function isStringNotEmpty($param): bool
    {
        return \is_string($param) && ! empty($param);
    }

    /**
     * Return true if there is no upload error.
     *
     * @return bool
     */
    private function isOk(): bool
    {
        return $this->error === \UPLOAD_ERR_OK;
    }

    /**
     * Validate retrieve stream.
     *
     * @throws \Viserio\Component\Contract\Http\Exception\RuntimeException if is moved or not ok
     *
     * @return void
     */
    private function validateActive(): void
    {
        if ($this->isOk() === false) {
            throw new RuntimeException(\sprintf(
                'Cannot retrieve stream due to upload error: %s',
                self::$errorMessages[$this->error]
            ));
        }

        if ($this->isMoved()) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved.');
        }
    }
}
