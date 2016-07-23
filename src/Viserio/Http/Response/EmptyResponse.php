<?php

declare(strict_types=1);
namespace Viserio\Http\Response;

use Viserio\Http\Response;
use Viserio\Http\Stream;

class EmptyResponse extends Response
{
    /**
     * Create an empty response with the given status code.
     *
     * @param array $headers Headers for the response, if any.
     * @param int   $status  Status code for the response, if any.
     */
    public function __construct(array $headers = [], int $status = 204)
    {
        parent::__construct(
            $status,
            $headers,
            new Stream(fopen('php://temp', 'r'))
        );
    }
}
