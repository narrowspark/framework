<?php
namespace Viserio\Exception\Adapter;

use Symfony\Component\HttpFoundation\Response;
use Viserio\Contracts\Exception\Adapter;
use Viserio\Contracts\Http\HttpExceptionInterface;
use Whoops\Run;

/**
 * WhoopsDisplayer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
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
