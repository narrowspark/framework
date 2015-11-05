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
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * SymfonyDisplayer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
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
