<?php
namespace Viserio\Http\JsonResponse;

use Viserio\Http\JsonResponse;

/**
 * ResetContentJsonResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
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
