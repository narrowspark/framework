<?php
namespace Viserio\Http\JsonResponse;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Viserio\Http\JsonResponse;

/**
 * RawJsonResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8-dev
 */
class RawJsonResponse extends JsonResponse
{
    /**
     * Constructor.
     *
     * @param string|null $data    The raw JSON data
     * @param int         $status  The status code (defaults to 200)
     * @param array       $headers An array of response headers
     */
    public function __construct($data = null, $status = 200, array $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->setData($data);
    }

    /**
     * Sets the raw JSON data on the response object.
     *
     * @param string|null|array $data The raw JSON data
     *
     * @return JsonResponse|null
     */
    public function setData($data = [])
    {
        $this->data = (string) $data;
        $this->update();
    }
}
