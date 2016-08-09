<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

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
     * Set the URI that the route responds to.
     *
     * @param string $uri
     *
     * @return $this
     */
    public function setUri(string $uri): Route;

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
     * Determine if the route only responds to HTTP requests.
     *
     * @return bool
     */
    public function httpOnly(): bool;

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function httpsOnly(): bool;

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
     * @return array
     *
     * @throws \LogicException
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
     * Check if route is a static route.
     *
     * @return bool
     */
    public function isStatic(): bool;

    /**
     * Run the route action and return the response.
     *
     * @return mixed
     */
    public function run();

    /**
     * Set the router instance on the route.
     *
     * @param \Viserio\Contracts\Routing\Router $router
     *
     * @return $this
     */
    public function setRouter(Router $router): Route;
}
