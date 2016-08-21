<?php
declare(strict_types=1);
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ServerMiddleware extends Middleware
{
    /**
     * Process a server request and return a response.
     *
     * Takes the incoming request and optionally modifies it before delegating
     * to the next frame to get a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Viserio\Contracts\Middleware\Delegate   $frame
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        Delegate $frame
    ): ResponseInterface;
}
