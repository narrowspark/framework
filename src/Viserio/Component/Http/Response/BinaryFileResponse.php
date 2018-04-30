<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Response;

use DateTime;
use DateTimeImmutable;
use Psr\Http\Message\StreamInterface;
use SplFileInfo;
use Viserio\Component\Contract\Http\Exception\FileException;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\LogicException;
use Viserio\Component\Http\AbstractMessage;
use Viserio\Component\Http\File\File;
use Viserio\Component\Http\Response;

class BinaryFileResponse extends Response
{
    /**
     * A SplFileInfo instance.
     *
     * @var \SplFileInfo
     */
    protected $file;

    protected $offset;

    protected $maxlen;

    /**
     * Should the file be deleted after send.
     *
     * @var bool
     */
    protected $deleteFileAfterSend = false;

    /**
     * @param \SplFileInfo|\Viserio\Component\Http\File\File|string $file               The file to stream
     * @param int                                                   $status             The response status code
     * @param array                                                 $headers            An array of response headers
     * @param bool                                                  $public             Files are public by default
     * @param null|string                                           $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool                                                  $autoEtag           Whether the ETag header should be automatically set
     * @param bool                                                  $autoLastModified   Whether the Last-Modified header should be automatically set
     *
     * @throws \Viserio\Component\Contract\Http\Exception\FileNotFoundException
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Http\Exception\FileException
     */
    public function __construct(
        $file,
        int $status = 200,
        array $headers = [],
        bool $public = true,
        string $contentDisposition = null,
        bool $autoEtag = false,
        bool $autoLastModified = true
    ) {
        parent::__construct($status, $headers, null);

        $this->setFile($file);

        if ($autoEtag) {
            $this->setAutoEtag();
        }

        if ($autoLastModified) {
            $this->setAutoLastModified();
        }

        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): AbstractMessage
    {
        throw new LogicException('The content cannot be set on a BinaryFileResponse instance.');
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     *
     * @return void
     */
    protected function setAutoLastModified(): void
    {
        $date = DateTime::createFromFormat('U', $this->file->getMTime());
        $date = DateTimeImmutable::createFromMutable($date);
        $date = $date->setTimezone(new \DateTimeZone('UTC'));

        $this->withHeader('last-modified', $date->format('D, d M Y H:i:s').' GMT');
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     *
     * @return void
     */
    protected function setAutoEtag(): void
    {
        $etag = \base64_encode(\hash_file('sha256', $this->file->getPathname(), true));

        if (\strpos($etag, '"') !== 0) {
            $etag = '"'.$etag.'"';
        }

        $this->withHeader('etag', $etag);
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition      ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename         Optionally use this UTF-8 encoded filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return void
     */
    protected function setContentDisposition($disposition, $filename = '', $filenameFallback = '')
    {
        if ($filename === '') {
            $filename = $this->file->getFilename();
        }

        if ($filenameFallback === '' && (! \preg_match('/^[\x20-\x7e]*$/', $filename) || \mb_strpos($filename, '%') !== false)) {
            $encoding = \mb_detect_encoding($filename, null, true) ?: '8bit';

            for ($i = 0, $filenameLength = \mb_strlen($filename, $encoding); $i < $filenameLength; $i++) {
                $char = \mb_substr($filename, $i, 1, $encoding);

                if ($char === '%' || \ord($char) < 32 || \ord($char) > 126) {
                    $filenameFallback .= '_';
                } else {
                    $filenameFallback .= $char;
                }
            }
        }

        $dispositionHeader = $this->makeDisposition($disposition, $filename, $filenameFallback);

        $this->withHeader('content-disposition', $dispositionHeader);
    }

    /**
     * @param \SplFileInfo|\Viserio\Component\Http\File\File|string $file
     *
     * @throws \Viserio\Component\Contract\Http\Exception\FileNotFoundException
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Http\Exception\FileException
     */
    protected function setFile($file): void
    {
        if (!$file instanceof File) {
            if ($file instanceof SplFileInfo) {
                $file = new File($file->getPathname());
            } elseif (\is_string($file)) {
                $file = new File($file);
            } else {
                throw new InvalidArgumentException(\sprintf(
                    'Invalid content (%s) provided to %s',
                    (\is_object($file) ? \get_class($file) : \gettype($file)),
                    __CLASS__
                ));
            }
        }

        if (!$file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;
    }
}
