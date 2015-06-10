<?php

namespace Brainwave\Exception\Adapter;

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

use Brainwave\Contracts\Exception\Adapter;
use Brainwave\Contracts\Http\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Run;

/**
 * WhoopsDisplayer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class WhoopsDisplayer implements Adapter
{
    /**
     * The Whoops run instance.
     *
     * @var \Whoops\Run
     */
    protected $whoops;

    /**
     * Indicates if the application is in a console environment.
     *
     * @var bool
     */
    protected $runningInConsole;

    /**
     * Create a new Whoops exception displayer.
     *
     * @param \Whoops\Run $whoops
     * @param bool        $runningInConsole
     */
    public function __construct(Run $whoops, $runningInConsole)
    {
        $this->whoops = $whoops;
        $this->runningInConsole = $runningInConsole;
    }

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

        return new Response($this->whoops->handleException($exception), $status, $headers);
    }
}
