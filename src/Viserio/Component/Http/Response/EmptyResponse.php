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

class EmptyResponse extends Response
{
    /**
     * Create an empty response with the given status code.
     *
     * @param array<int|string, mixed> $headers headers for the response, if any
     * @param int                      $status  status code for the response, if any
     * @param string                   $version protocol version
     *
     * @throws \Narrowspark\HttpStatus\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Http\Exception\UnexpectedValueException
     */
    public function __construct(array $headers = [], int $status = self::STATUS_NO_CONTENT, string $version = '1.1')
    {
        /** @var resource $handle */
        $handle = \fopen('php://temp', 'rb');

        parent::__construct(
            $status,
            $headers,
            new Stream($handle),
            $version
        );
    }
}
