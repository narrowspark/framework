<?php
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Middleware
{
    /**
     * Process an incoming request and/or response.
     *
     * Accepts a server-side request and a response instance, and does
     * something with them.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  the request
     * @param \Viserio\Contracts\Middleware\Frame      $frame
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Frame $frame): ResponseInterface;
}
