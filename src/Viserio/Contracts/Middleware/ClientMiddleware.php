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
     * @param \Psr\Http\Message\RequestInterface     $request
     * @param \Viserio\Contracts\Middleware\Delegate $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        RequestInterface $request,
        Delegate $next
    ): ResponseInterface;
}
