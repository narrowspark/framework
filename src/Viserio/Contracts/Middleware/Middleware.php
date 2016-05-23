<?php
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface Middleware
{
    /**
     * Process an incoming request and/or response.
     *
     * Accepts a server-side request and a response instance, and does
     * something with them.
     *
     * If the response is not complete and/or further processing would not
     * interfere with the work done in the middleware, or if the middleware
     * wants to delegate to another process, it can use the `$next` callable
     * if present.
     *
     * If the middleware does not return a value, execution of the current
     * request is considered complete, and the response instance provided will
     * be considered the response to return.
     *
     * Alternately, the middleware may return a response instance.
     *
     * Often, middleware will `return $next();`, with the assumption that a
     * later middleware will return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  the request
     * @param \Psr\Http\Message\ResponseInterface      $response the response
     * @param callable|MiddlewareInterface             $next     the next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next): \Psr\Http\Message\ResponseInterface;
}
