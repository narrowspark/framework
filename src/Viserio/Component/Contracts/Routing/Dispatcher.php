<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;

interface Dispatcher
{
    /**
     * Match number for a not found route.
     *
     * @var int
     */
    public const NOT_FOUND = 0;

    /**
     * Match number for a found route.
     *
     * @var int
     */
    public const FOUND = 1;

    /**
     * Match number for a not allowed http method.
     *
     * @var int
     */
    public const HTTP_METHOD_NOT_ALLOWED = 2;

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Viserio\Component\Contracts\Routing\Route|null
     */
    public function getCurrentRoute(): ?Route;

    /**
     * Match and dispatch a route matching the given http method and uri.
     *
     * @param \Viserio\Component\Contracts\Routing\RouteCollection $routes
     * @param \Psr\Http\Message\ServerRequestInterface             $request
     *
     * @throws \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @throws \Narrowspark\HttpStatus\Exception\NotFoundException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(RouteCollectionContract $routes, ServerRequestInterface $request): ResponseInterface;
}
