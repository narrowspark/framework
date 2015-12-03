<?php
namespace Viserio\Http\JsonResponse;

use Viserio\Http\JsonResponse;

class NoContentJsonResponse extends JsonResponse
{
    /**
     * Constructor.
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        parent::__construct('', 204, $headers);
    }
}
