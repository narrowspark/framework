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
use Viserio\Component\Http\Stream;

class RedirectResponse extends Response
{
    /**
     * Create a redirect response.
     *
     * Produces a redirect response with a Location header and the given status
     * (302 by default).
     *
     * Note: this method overwrites the `location` $headers value.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri     uri for the Location header
     * @param int                                   $status  integer status code for the redirect; 302 by default
     * @param array<int|string, mixed>              $headers array of headers to use at initialization
     * @param string                                $version protocol version
     *
     * @throws \Viserio\Contract\Http\Exception\UnexpectedValueException
     */
    public function __construct($uri, int $status = self::STATUS_FOUND, array $headers = [], string $version = '1.1')
    {
        $headers['location'] = [(string) $uri];

        /** @var resource $handle */
        $handle = \fopen('php://temp', 'r+b');

        parent::__construct($status, $headers, new Stream($handle), $version);
    }
}
