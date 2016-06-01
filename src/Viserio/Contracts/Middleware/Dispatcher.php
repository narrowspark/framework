<?php
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Dispatcher
{
    /**
     * Add middleware to the pip-line.
     * Middleware should be called in the same order they are piped.
     *
     * A middleware implement the Middleware Interface.
     *
     * @param Middleware|callable(RequestInterface,FrameInterface):ResponseInterface $middleware
     *
     * @throws \InvalidArgumentException when adding a invalid middleware to the stack
     *
     * @return Dispatcher
     */
    public function pipe($middleware): Dispatcher;

    /**
     * @param ServerRequestInterface $request
     * @param callable               $default
     *
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request, callable $default): ResponseInterface;
}
