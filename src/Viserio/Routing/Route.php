<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Viserio\Contracts\{
    Container\Traits\ContainerAwareTrait,
    Routing\Route as RouteContract,
    Routing\RouteGroup as RouteGroupContract
};

class Route implements RouteContract
{
    use ContainerAwareTrait;

    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    protected $uri;

    /**
     * The HTTP methods the route responds to.
     *
     * @var string|string[]
     */
    protected $httpMethods;

    /**
     * The route action array.
     *
     * @var \Closure|array
     */
    protected $action;

    /**
     * The controller instance.
     *
     * @var mixed
     */
    protected $controller;

    /**
     * The default values for the route.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * The array of matched parameters.
     *
     * @var array|null
     */
    protected $parameters;

    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    protected $parameterNames;

    /**
     * The router instance used by the route.
     *
     * @var \Viserio\Contracts\Routing\Router
     */
    protected $router;

     /**
     * The route group instance used by the route.
     *
     * @var \Viserio\Contracts\Routing\RouteGroup
     */
    protected $group;

    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->action['domain'] ?? null;
    }

    /**
     * Get the URI associated with the route.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Set the URI that the route responds to.
     *
     * @param string $uri
     *
     * @return $this
     */
    public function setUri(string $uri): RouteContract
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get the name of the route instance.
     *
     * @return string
     */
    public function getName()
    {
        return $this->action['as'] ?? null;
    }

     /**
     * Add or change the route name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): RouteContract
    {
        $this->action['as'] = isset($this->action['as']) ? $this->action['as'] . $name : $name;

        return $this;
    }

    /**
     * Get the HTTP verbs the route responds to.
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->httpMethods;
    }

    /**
     * Determine if the route only responds to HTTP requests.
     *
     * @return bool
     */
    public function httpOnly(): bool
    {
        return in_array('http', $this->action, true);
    }

    /**
     * Determine if the route only responds to HTTPS requests.
     *
     * @return bool
     */
    public function httpsOnly(): bool
    {
        return in_array('https', $this->action, true);
    }

    /**
     * Get the action name for the route.
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->action['controller'] ?? 'Closure';
    }

    /**
     * Get the action array for the route.
     *
     * @return array
     */
    public function getAction(): array
    {
        return $this->action;
    }

    /**
     * Set the action array for the route.
     *
     * @param array $action
     *
     * @return $this
     */
    public function setAction(array $action): RouteContract
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the parent group.
     *
     * @return \Viserio\Contracts\Routing\RouteGroup
     */
    public function getParentGroup(): RouteGroupContract
    {
        return $this->group;
    }

    /**
     * Set the parent group.
     *
     * @param \Viserio\Contracts\Routing\RouteGroup $group
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function setParentGroup(RouteGroupContract $group): RouteContract
    {
        $this->group = $group;

        return $this;
    }
}
