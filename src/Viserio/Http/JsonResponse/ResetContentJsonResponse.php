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
 * ResetContentJsonResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
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
