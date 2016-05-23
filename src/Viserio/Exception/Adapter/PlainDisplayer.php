<?php
namespace Viserio\Exception\Adapter;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Viserio\Contracts\Exception\Adapter;
use Viserio\Contracts\Http\HttpExceptionInterface;

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
    public function display(Exception $exception, int $code): \Symfony\Component\HttpFoundation\Response
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        return new Response(file_get_contents(__DIR__ . '/../Resources/plain.html'), $status, $headers);
    }
}
