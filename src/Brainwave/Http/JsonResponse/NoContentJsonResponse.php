<?php

namespace Brainwave\Http\JsonResponse;

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

use Brainwave\Http\JsonResponse;

/**
 * NoContentJsonResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
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
