<?php
namespace Viserio\Exception\Displayers;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\Http\HttpExceptionInterface;

class HtmlDisplayer implements DisplayerContract
{
    /**
    * {@inheritdoc}
     */
    public function display($exception, int $code)
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        return new Response(file_get_contents(__DIR__ . '/../Resources/plain.html'), $status, $headers);
    }
}
