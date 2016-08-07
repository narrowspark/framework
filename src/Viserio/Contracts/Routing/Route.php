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
     * Get the parent group.
     *
     * @return \Viserio\Contracts\Routing\RouteGroup
     */
    public function getParentGroup(): RouteGroup;

    /**
     * Set the parent group.
     *
     * @param \Viserio\Contracts\Routing\RouteGroup $group
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function setParentGroup(RouteGroup $group): Route;
}
