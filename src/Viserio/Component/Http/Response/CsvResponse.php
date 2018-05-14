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
use Viserio\Component\Http\Response\Traits\InjectContentTypeTrait;
use Viserio\Component\Http\Stream;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

class CsvResponse extends Response
{
    use DownloadResponseTrait;
    use InjectContentTypeTrait;

    private const CONTENT_TYPE = 'text/csv; charset=utf-8';

    /**
     * Create a CSV response.
     *
     * Produces a CSV response with a Content-Type of text/csv and a default
     * status of 200.
     *
     * @param \Psr\Http\Message\StreamInterface|string $content  string or stream for the message body
     * @param int                                      $status   integer status code for the response; 200 by default
     * @param string                                   $filename
     * @param array                                    $headers  array of headers to use at initialization
     */
    public function __construct($content, int $status = 200, string $filename = '', array $headers = [])
    {
        if ($filename !== '') {
            $headers = $this->prepareDownloadHeaders($filename, $headers);
        }

        parent::__construct(
            $status,
            $this->injectContentType(self::CONTENT_TYPE, $headers),
            $this->createBody($content)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDownloadHeaders(string $filename): array
    {
        return [
            'cache-control' => ['must-revalidate'],
            'content-description' => ['File Transfer'],
            'content-disposition' => [\sprintf('attachment; filename=%s', $filename)],
            'content-transfer-encoding' => ['Binary'],
            'content-type' => [self::CONTENT_TYPE],
            'expires' => ['0'],
            'pragma' => ['Public'],
        ];
    }

    /**
     * Create the CSV message body.
     *
     * @param \Psr\Http\Message\StreamInterface|string $content
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if $content is neither a string or stream
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function createBody($content): StreamInterface
    {
        if ($content instanceof StreamInterface) {
            return $content;
        }

        if (! \is_string($content)) {
            throw new InvalidArgumentException(\sprintf('Invalid CSV content (%s) provided to %s.', (\is_object($content) ? \get_class($content) : \gettype($content)), __CLASS__));
        }

        $body = new Stream('php://temp', ['mode' => 'wb+']);
        $body->write($content);
        $body->rewind();

        return $body;
    }
}
