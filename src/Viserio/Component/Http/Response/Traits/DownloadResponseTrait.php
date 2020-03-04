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

namespace Viserio\Component\Http\Response\Traits;

use Viserio\Contract\Http\Exception\InvalidArgumentException;

trait DownloadResponseTrait
{
    /**
     * The filename to be sent with the response.
     *
     * @var string
     */
    protected $filename;

    /**
     * The content type to be sent with the response.
     *
     * @var string
     */
    protected $contentType;

    /**
     * A list of header keys required to be sent with a download response.
     *
     * @var string[]
     */
    protected static $downloadResponseHeaders = [
        'cache-control',
        'content-description',
        'content-disposition',
        'content-transfer-encoding',
        'expires',
        'pragma',
    ];

    /**
     * Check if the extra headers contain any of the download headers.
     *
     * The download headers cannot be overridden.
     *
     * @param string[] $downloadHeaders
     * @param string[] $headers
     */
    public function overridesDownloadHeaders(array $downloadHeaders, array $headers = []): bool
    {
        $overridesDownloadHeaders = false;

        foreach (\array_keys($headers) as $header) {
            if (\in_array($header, $downloadHeaders, true)) {
                $overridesDownloadHeaders = true;

                break;
            }
        }

        return $overridesDownloadHeaders;
    }

    /**
     * Prepare download response headers.
     *
     * @param string[] $headers
     *
     * @return string[]
     */
    protected function prepareDownloadHeaders(array $headers = []): array
    {
        if ($this->overridesDownloadHeaders(static::$downloadResponseHeaders, $headers)) {
            throw new InvalidArgumentException(\sprintf('Cannot override download headers (%s) when download response is being sent', \implode(', ', static::$downloadResponseHeaders)));
        }

        return \array_merge(
            $headers,
            $this->getDownloadHeaders(),
            [
                'content-disposition' => \sprintf('attachment; filename=%s', $this->filename),
                'content-type' => $this->contentType,
            ]
        );
    }

    /**
     * Get download headers.
     *
     * @return string[]
     */
    private function getDownloadHeaders(): array
    {
        $headers = [];
        $headers['cache-control'] = 'must-revalidate';
        $headers['content-description'] = 'File Transfer';
        $headers['content-transfer-encoding'] = 'Binary';
        $headers['expires'] = '0';
        $headers['pragma'] = 'Public';

        return $headers;
    }
}
