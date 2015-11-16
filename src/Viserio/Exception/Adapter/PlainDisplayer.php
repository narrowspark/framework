<?php
namespace Viserio\Exception\Adapter;

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

use Viserio\Contracts\Exception\Adapter;
use Viserio\Contracts\Http\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * PlainDisplayer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class PlainDisplayer implements Adapter
{
    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     *
     * @return Response
     */
    public function display(\Exception $exception, $code)
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        return new Response(file_get_contents(__DIR__.'/../Resources/plain.html'), $status, $headers);
    }
}
