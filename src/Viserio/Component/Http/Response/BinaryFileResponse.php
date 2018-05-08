<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Response;

use DateTime;
use DateTimeImmutable;
use Narrowspark\Http\Message\Util\InteractsWithDisposition;
use Psr\Http\Message\StreamInterface;
use SplFileInfo;
use Viserio\Component\Contract\Http\Exception\FileException;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\LogicException;
use Viserio\Component\Http\AbstractMessage;
use Viserio\Component\Http\File\File;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Util;

class BinaryFileResponse extends Response
{
    /**
     * @var string
     */
    public const DISPOSITION_ATTACHMENT = 'attachment';

    /**
     * @var string
     */
    public const DISPOSITION_INLINE = 'inline';

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $maxlen = -1;

    /**
     * Should the file be deleted after send.
     *
     * @var bool
     */
    protected $deleteFileAfterSend = false;

    /**
     * A Viserio Http File instance.
     *
     * @var \Viserio\Component\Http\File\File
     */
    protected $file;

    /**
     * @param \SplFileInfo|string|\Viserio\Component\Http\File\File $file               The file to stream
     * @param string                                                $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param string                                                $filenameFallback   A string containing only ASCII characters that
     *                                                                                  is semantically equivalent to $filename. If the filename is already ASCII,
     *                                                                                  it can be omitted, or just copied from $filename
     * @param int                                                   $status             The response status code
     * @param bool                                                  $public             Files are public by default
     * @param bool                                                  $autoEtag           Whether the ETag header should be automatically set
     * @param bool                                                  $autoLastModified   Whether the Last-Modified header should be automatically set
     *
     * @throws \Viserio\Component\Contract\Http\Exception\FileNotFoundException
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Http\Exception\FileException
     */
    public function __construct(
        $file,
        string $contentDisposition,
        string $filenameFallback = '',
        int $status = 200,
        bool $public = true,
        bool $autoEtag = false,
        bool $autoLastModified = true
    ) {
        $this->setFile($file);

        $headers = [];

        if ($autoEtag === true) {
            $this->setAutoEtag($headers);
        }

        if ($autoLastModified === true) {
            $this->setAutoLastModified($headers);
        }

        parent::__construct($status, $headers, null);

        $this->setContentDisposition($contentDisposition, $this->file->getFilename(), $filenameFallback);
    }

    /**
     * If this is set to true, the file will be unlinked after the request is send
     * Note: If the X-Sendfile header is used, the deleteFileAfterSend setting will not be used.
     *
     * @param bool $shouldDelete
     *
     * @return $this
     */
    public function deleteFileAfterSend(bool $shouldDelete = true): self
    {
        $this->deleteFileAfterSend = $shouldDelete;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): AbstractMessage
    {
        throw new LogicException('The content cannot be set on a BinaryFileResponse instance.');
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        $fileStream = new Stream(\fopen($this->file->getPathname(), 'rb'));
        $outStream  = new Stream(\fopen('php://output', 'wb'));

        Util::copyToStream($fileStream, $outStream, $this->maxlen);

        $fileStream->close();

        if ($this->deleteFileAfterSend) {
            unlink($this->file->getPathname());
        }

        return $outStream;
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     *
     * @param array $headers
     *
     * @return array
     */
    protected function setAutoLastModified($headers): array
    {
        $date = DateTime::createFromFormat('U', (string) $this->file->getMTime());
        $date = DateTimeImmutable::createFromMutable($date);
        $date = $date->setTimezone(new \DateTimeZone('UTC'));

        $headers['last-modified'] = $date->format('D, d M Y H:i:s') . ' GMT';

        return $headers;
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     *
     * @param array $headers
     *
     * @return array
     */
    protected function setAutoEtag(array $headers): array
    {
        $eTag = \base64_encode(\hash_file('sha256', $this->file->getPathname(), true));

        if (\mb_strpos($eTag, '"') !== 0) {
            $eTag = '"' . $eTag . '"';
        }

        $headers['etag'] = $eTag;

        return $headers;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename Optionally use this UTF-8 encoded filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function setContentDisposition($disposition, $filename = '', $filenameFallback = ''): void
    {
        if ($filenameFallback === '') {
            $filenameFallback = InteractsWithDisposition::encodedFallbackFilename($filename);
        }

        InteractsWithDisposition::makeDisposition($this, $disposition, $filename, $filenameFallback);
    }

    /**
     * Transform a SplFileInfo to a Http File and check if the file exists.
     *
     * @param \SplFileInfo|string|\Viserio\Component\Http\File\File $file
     *
     * @throws \Viserio\Component\Contract\Http\Exception\FileNotFoundException
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Http\Exception\FileException
     *
     * @return void
     */
    protected function setFile($file): void
    {
        if (! $file instanceof File) {
            if ($file instanceof SplFileInfo) {
                $file = new File($file->getPathname());
            } elseif (\is_string($file)) {
                $file = new File($file);
            } else {
                throw new InvalidArgumentException(\sprintf(
                    'Invalid content (%s) provided to %s.',
                    (\is_object($file) ? \get_class($file) : \gettype($file)),
                    __CLASS__
                ));
            }
        }

        if (! $file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;
    }
}
