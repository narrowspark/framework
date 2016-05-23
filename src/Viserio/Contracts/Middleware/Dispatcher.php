<?php
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface Dispatcher
{
    /**
     * Add middleware to the pip-line.
     * Middleware should be called in the same order they are piped.
     *
     * A middleware CAN implement the Middleware Interface, but MUST be
     * callable. A middleware WILL be called with three parameters:
     * Request, Response and Next.
     *
     *
     * @param callable $middleware
     *
     * @throws \RuntimeException when adding middleware to the stack to late
     */
    public function pipe(callable $middleware);

    /**
     * Dispatches to a new Runner.
     *
     * @param Request  $request  The request.
     * @param Response $response The response.
     *
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response): \Viserio\Contracts\Middleware\ResponseInterface;
}
