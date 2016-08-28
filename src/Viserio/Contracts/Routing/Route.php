<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

interface Route
{
    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function getDomain();

    /**
     * Get the URI that the route responds to.
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get the name of the route instance.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Add or change the route name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): Route;

    /**
     * Get the HTTP verbs the route responds to.
     *
     * @return array
     */
    public function getMethods(): array;

    /**
     * Set a regular expression requirement on the route.
     *
     * @param array|string $name
     * @param string|null  $expression
     *
     * @return $this
     */
    public function where($name, string $expression = null): Route;

    /**
     * Add a middleware to route.
     *
     * @return $this
     */
    public function withMiddleware(MiddlewareContract $middleware): Route;

    /**
     * Remove a middleware from route.
     *
     * @return $this
     */
    public function withoutMiddleware(MiddlewareContract $middleware): Route;

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware(): array;

    /**
     * Determine if the route only responds to HTTP requests.
     *
     * @return bool
     */
    public function isHttpOnly(): bool;

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function isHttpsOnly(): bool;

    /**
     * Get the action name for the route.
     *
     * @return string
     */
    public function getActionName(): string;

    /**
     * Get the action array for the route.
     *
     * @return array
     */
    public function getAction(): array;

    /**
     * Set the action array for the route.
     *
     * @param array $action
     *
     * @return $this
     */
    public function setAction(array $action): Route;

    /**
     * Add a prefix to the route URI.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function addPrefix(string $prefix): Route;

    /**
     * Get the prefix of the route instance.
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Set a parameter to the given value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameter($name, $value): Route;

    /**
     * Get a given parameter from the route.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return string|object
     */
    public function getParameter(string $name, $default = null);

    /**
     * Determine a given parameter exists from the route.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter(string $name): bool;

    /**
     * Get the key / value list of parameters for the route.
     *
     * @throws \LogicException
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Determine if the route has parameters.
     *
     * @return bool
     */
    public function hasParameters(): bool;

    /**
     * Unset a parameter on the route if it is set.
     *
     * @param string $name
     */
    public function forgetParameter(string $name);

    /**
     * The regular expression requirements.
     *
     * @return \Viserio\Contracts\Routing\RouteMatcher[]
     */
    public function getSegments(): array;

    /**
     * Run the route action and return the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run(ServerRequestInterface $request): ResponseInterface;
}
