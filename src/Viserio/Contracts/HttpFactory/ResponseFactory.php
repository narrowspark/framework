<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory;

interface ResponseFactory
{
    /**
     * Create a new response.
     *
     * @param int $code HTTP status code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createResponse($code = 200);
}
