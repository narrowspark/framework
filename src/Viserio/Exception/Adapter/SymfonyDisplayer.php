<?php
namespace Viserio\Exception\Adapter;

use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Viserio\Contracts\Exception\Adapter;
use Viserio\Contracts\Http\HttpExceptionInterface;

class SymfonyDisplayer implements Adapter
{
    /**
     * The Symfony exception handler.
     *
     * @var \Symfony\Component\Debug\ExceptionHandler
     */
    protected $symfony;

    /**
     * Indicates if JSON should be returned.
     *
     * @var bool
     */
    protected $returnJson;

    /**
     * Create a new Symfony exception displayer.
     *
     * @param \Symfony\Component\Debug\ExceptionHandler $symfony
     * @param bool                                      $returnJson
     */
    public function __construct(ExceptionHandler $symfony, $returnJson = false)
    {
        $this->symfony = $symfony;
        $this->returnJson = $returnJson;
    }

    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     *
     * @return JsonResponse|null
     */
    public function display(\Exception $exception, $code)
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($this->returnJson) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ], $status);
        }

        $this->symfony->sendPhpResponse($exception);
    }
}
