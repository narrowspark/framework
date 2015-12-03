<?php
namespace Viserio\Http\JsonResponse;

use Viserio\Http\JsonResponse;

class ResetContentJsonResponse extends JsonResponse
{
    /**
     * Constructor.
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        parent::__construct('', 205, $headers);
    }
}
