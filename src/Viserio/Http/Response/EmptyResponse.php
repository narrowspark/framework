<?php
namespace Viserio\Http\Response;

use Viserio\Http\Response;
use Viserio\Http\Stream;

class EmptyResponse extends Response
{
    /**
     * Create an empty response with the given status code.
     *
     * @param int   $status  Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct($status = 204, array $headers = [])
    {
        parent::__construct(
            new Stream('php://temp', 'r'),
            $status,
            $headers
        );
    }

    /**
     * Create an empty response with the given headers.
     *
     * @param array $headers Headers for the response.
     *
     * @return EmptyResponse
     */
    public static function withHeaders(array $headers)
    {
        return new static(204, $headers);
    }
}
