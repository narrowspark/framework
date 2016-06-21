<?php
namespace Viserio\Exception\Displayers;

use Exception;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\Http\HttpExceptionInterface;
use Whoops\Run;

class WhoopsDisplayer implements DisplayerContract
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
    * {@inheritdoc}
     */
    public function display($exception, int $code)
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        return new Response($this->whoops->handleException($exception), $status, $headers);
    }
}
