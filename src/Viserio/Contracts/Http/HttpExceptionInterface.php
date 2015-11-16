<?php
namespace Viserio\Contracts\Http;

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

/**
 * HttpExceptionInterface.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface HttpExceptionInterface
{
    /**
     * Return the status code of the http exceptions.
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Return an array of headers provided when the exception was thrown.
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Returns a response built from the thrown exception.
     *
     * @return \Viserio\Http\JsonResponse
     */
    public function getJsonResponse();
}
