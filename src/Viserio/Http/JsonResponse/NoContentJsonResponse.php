<?php
namespace Viserio\Http\JsonResponse;

use Viserio\Http\JsonResponse;

/**
 * NoContentJsonResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
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
