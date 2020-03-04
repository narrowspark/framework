<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Http\Response;

use Viserio\Component\Http\Response;
use Viserio\Component\Http\Response\Traits\CreateBodyTrait;
use Viserio\Component\Http\Response\Traits\DownloadResponseTrait;
use Viserio\Component\Http\Stream;

class DownloadResponse extends Response
{
    use DownloadResponseTrait;
    use CreateBodyTrait;

    /** @var string */
    private const DEFAULT_CONTENT_TYPE = 'application/octet-stream';

    /**
     * Create a new DownloadResponse instance.
     *
     * @param \Psr\Http\Message\StreamInterface|string $body        string or stream for the message body
     * @param string                                   $filename    The file name to be sent with the response
     * @param int                                      $status      integer status code for the response; 200 by default
     * @param string                                   $contentType The content type to be sent with the response
     * @param array<int|string, mixed>                 $headers     An array of optional headers. These cannot override those set in getDownloadHeaders
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
}
