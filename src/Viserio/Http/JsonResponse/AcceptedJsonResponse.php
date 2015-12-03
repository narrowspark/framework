<?php
namespace Viserio\Http\JsonResponse;

use Viserio\Http\JsonResponse;

/**
 * AcceptedJsonResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class AcceptedJsonResponse extends JsonResponse
{
    /**
     * Constructor.
     *
     * @param string|array|null $data
     * @param array             $headers
     */
    public function __construct($data = null, array $headers = [])
    {
        parent::__construct($data, 202, $headers);
    }
}
