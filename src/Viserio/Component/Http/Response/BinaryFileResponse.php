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

namespace Viserio\Component\Http\Response;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use ErrorException;
use Narrowspark\Http\Message\Util\InteractsWithDisposition;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SplFileInfo;
use Viserio\Component\Http\File\File;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\FileException;
use Viserio\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Contract\Http\Exception\LogicException;

class BinaryFileResponse extends Response
{
    /** @var string */
    public const DISPOSITION_ATTACHMENT = 'attachment';

    /** @var string */
    public const DISPOSITION_INLINE = 'inline';

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
     * @param SplFileInfo|string|\Viserio\Component\Http\File\File $file               The file to stream
     *                                                                                 is semantically equivalent to $filename. If the filename is already ASCII,
     *                                                                                 it can be omitted, or just copied from $filename
     * @param int                                                  $status             The response status code
     * @param array<int|string, mixed>                             $headers            An array of response headers
     * @param null|string                                          $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool                                                 $autoETag           Whether the ETag header should be automatically set
     * @param bool                                                 $autoLastModified   Whether the Last-Modified header should be automatically set
     *
     * @throws \Viserio\Contract\Http\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Http\Exception\FileException
     */
    public function __construct(
        $file,
        int $status = self::STATUS_OK,
        array $headers = [],
        ?string $contentDisposition = null,
        bool $autoETag = false,
        bool $autoLastModified = true
    ) {
        parent::__construct($status, $headers, null);

        $this->setFile($file, $contentDisposition, $autoETag, $autoLastModified);
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
     * Gets the file.
     *
     * @return \Viserio\Component\Http\File\File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * Transform a SplFileInfo to a Http File and check if the file exists.
     *
     * @param mixed  $file
     * @param string $contentDisposition
     * @param bool   $autoETag
     * @param bool   $autoLastModified
     *
     * @throws \Viserio\Contract\Http\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Http\Exception\FileException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function setFile(
        $file,
        ?string $contentDisposition = null,
        bool $autoETag = false,
        bool $autoLastModified = true
    ): ResponseInterface {
        $isFile = ! $file instanceof File;

        if ($isFile && $file instanceof SplFileInfo) {
            $file = new File($file->getPathname());
        } elseif ($isFile && \is_string($file)) {
            $file = new File($file);
        } else {
            throw new InvalidArgumentException(\sprintf('Invalid content [%s] provided to %s.', (\is_object($file) ? \get_class($file) : \gettype($file)), __CLASS__));
        }

        if (! $file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;

        if ($autoETag === true) {
            $this->setAutoEtag();
        }

        if ($autoLastModified === true) {
            $this->setAutoLastModified();
        }

        if ($contentDisposition !== null) {
            $this->headers['Content-Length'] = [$this->file->getSize()];
            $this->headers['Content-Disposition'] = [
                InteractsWithDisposition::makeDisposition(
                    $contentDisposition,
                    $this->file->getFilename(),
                    InteractsWithDisposition::encodedFallbackFilename($this->file->getFilename())
                ),
            ];

            $this->headerNames['content-length'] = 'Content-Length';
            $this->headerNames['content-disposition'] = 'Content-Disposition';

            if (! $this->hasHeader('Content-Type')) {
                $this->headers['Content-Type'] = [$this->file->getMimeType() ?? 'application/octet-stream'];
                $this->headerNames['content-type'] = 'Content-Type';
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        throw new LogicException('The content cannot be set on a BinaryFileResponse instance.');
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        $fileStream = new Stream($this->file->getPathname(), ['mode' => 'rb']);
        $outStream = new Stream('php://output', ['mode' => 'wb']);

        Util::copyToStream($fileStream, $outStream);

        $fileStream->close();

        if ($this->deleteFileAfterSend) {
            \unlink($this->file->getPathname());
        }

        return $outStream;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition      ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename         Optionally use this UTF-8 encoded filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function setContentDisposition(
        string $disposition,
        string $filename = '',
        string $filenameFallback = ''
    ): ResponseInterface {
        if ($filenameFallback === '') {
            $filenameFallback = InteractsWithDisposition::encodedFallbackFilename($filename);
        }

        return InteractsWithDisposition::appendDispositionHeader($this, $disposition, $filename, $filenameFallback);
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     *
     * @throws ErrorException
     *
     * @return void
     */
    protected function setAutoLastModified(): void
    {
        $datetime = DateTime::createFromFormat('U', (string) $this->file->getMTime());

        if ($datetime === false) {
            $error = \error_get_last();

            throw new ErrorException($error['message'] ?? 'An error occured', 0, $error['type'] ?? 1);
        }

        $date = DateTimeImmutable::createFromMutable($datetime);
        $date = $date->setTimezone(new DateTimeZone('UTC'));

        $this->headers['Last-Modified'] = [$date->format('D, d M Y H:i:s') . ' GMT'];
        $this->headerNames['last-modified'] = 'Last-Modified';
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     *
     * @return void
     */
    protected function setAutoEtag(): void
    {
        $eTag = \base64_encode(\hash_file('sha256', $this->file->getPathname(), true));

        if (\strpos($eTag, '"') !== 0) {
            $eTag = '"' . $eTag . '"';
        }

        $this->headers['Etag'] = [$eTag];
        $this->headerNames['etag'] = 'Etag';
    }
}
