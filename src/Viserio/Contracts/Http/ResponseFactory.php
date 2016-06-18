<?php
namespace Viserio\Contracts\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

interface ResponseFactory
{
    /**
     * Creates a new PSR-7 response.
     *
     * @param int $code
     *
     * @return ResponseInterface
     */
    public function createResponse(
        int $code = 200
    ): ResponseInterface;
}
