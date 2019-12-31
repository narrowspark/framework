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

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Response\Traits\DownloadResponseTrait;
use Viserio\Component\Http\Stream;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

class DownloadResponse extends Response
{
    use DownloadResponseTrait;

    /** @var string */
    private const DEFAULT_CONTENT_TYPE = 'application/octet-stream';

    /**
     * Create a new DownloadResponse instance.
     *
     * @param \Psr\Http\Message\StreamInterface|string $body        string or stream for the message body
     * @param string                                   $filename    The file name to be sent with the response
     * @param int                                      $status      integer status code for the response; 200 by default
     * @param string                                   $contentType The content type to be sent with the response
     * @param array                                    $headers     An array of optional headers. These cannot override those set in getDownloadHeaders
     * @param string                                   $version     protocol version
     */
    public function __construct(
        $body,
        string $filename,
        int $status = 200,
        string $contentType = self::DEFAULT_CONTENT_TYPE,
        array $headers = [],
        string $version = '1.1'
    ) {
        $this->filename = $filename;
        $this->contentType = $contentType;

        parent::__construct(
            $status,
            $this->prepareDownloadHeaders($headers),
            $this->createBody($body),
            $version
        );
    }

    /**
     * @param \Psr\Http\Message\StreamInterface|string $content
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if $body is neither a string nor a Stream
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function createBody($content): StreamInterface
    {
        if ($content instanceof StreamInterface) {
            return $content;
        }

        if (! \is_string($content)) {
            throw new InvalidArgumentException(\sprintf('Invalid content (%s) provided to %s', (\is_object($content) ? \get_class($content) : \gettype($content)), __CLASS__));
        }

        $body = new Stream('php://temp', ['mode' => 'wb+']);
        $body->write($content);
        $body->rewind();

        return $body;
    }
}
