<?php

declare(strict_types=1);
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientMiddleware extends Middleware
{
    /**
     * Process a client request and return a response.
     *
     * Takes the incoming request and optionally modifies it before delegating
     * to the next frame to get a response.
     *
     * @param RequestInterface $request
     * @param Frame            $next
     *
     * @return ResponseInterface
     */
    public function process(
        RequestInterface $request,
        Frame $next
    ): ResponseInterface;
}
