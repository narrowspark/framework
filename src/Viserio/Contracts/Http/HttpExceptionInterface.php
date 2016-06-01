<?php
namespace Viserio\Contracts\Http;

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
