<?php
declare(strict_types=1);
namespace Viserio\Contracts\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Stack
{
    /**
     * Return an instance with the specified middleware added to the stack.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the stack, and MUST return an instance that contains
     * the specified middleware.
     *
     * @param \Interop\Http\Middleware\MiddlewareInterface|\Interop\Http\Middleware\ServerMiddlewareInterface $middleware
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function withMiddleware($middleware): Stack;

    /**
     * Return an instance without the specified middleware.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the stack, and MUST return an instance that does not
     * contain the specified middleware.
     *
     * @param \Interop\Http\Middleware\MiddlewareInterface|\Interop\Http\Middleware\ServerMiddlewareInterface $middleware
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function withoutMiddleware($middleware): Stack;

    /**
     * Process the request through middleware and return the response.
     *
     * This method MUST be implemented in such a way as to allow the same
     * stack to be reused for processing multiple requests in sequence.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(RequestInterface $request): ResponseInterface;
}
