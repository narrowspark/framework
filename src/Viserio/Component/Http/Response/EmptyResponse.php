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

use Viserio\Component\Http\Response;
use Viserio\Component\Http\Stream;

class EmptyResponse extends Response
{
    /**
     * Create an empty response with the given status code.
     *
     * @param array  $headers headers for the response, if any
     * @param int    $status  status code for the response, if any
     * @param string $version protocol version
     *
     * @throws \Narrowspark\HttpStatus\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Http\Exception\UnexpectedValueException
     */
    public function __construct(array $headers = [], int $status = self::STATUS_NO_CONTENT, string $version = '1.1')
    {
        parent::__construct(
            $status,
            $headers,
            new Stream(\fopen('php://temp', 'rb')),
            $version
        );
    }
}
