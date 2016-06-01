<?php
namespace Viserio\Exception\Adapter;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Viserio\Contracts\Exception\Adapter;
use Viserio\Contracts\Http\HttpExceptionInterface;
use Whoops\Run;

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
    public function __construct(Run $whoops, bool $runningInConsole)
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
    public function display(Exception $exception, int $code): \Symfony\Component\HttpFoundation\Response
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        return new Response($this->whoops->handleException($exception), $status, $headers);
    }
}
