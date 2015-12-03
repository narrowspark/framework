<?php
namespace Viserio\Http\JsonResponse;

use Viserio\Http\JsonResponse;

/**
 * NonAuthoritativeInformationJsonResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class NonAuthoritativeInformationJsonResponse extends JsonResponse
{
    /**
     * Constructor.
     *
     * @param string|array|null $data
     * @param array             $headers
     */
    public function __construct($data = null, array $headers = [])
    {
        parent::__construct($data, 203, $headers);
    }
}
