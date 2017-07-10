<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Route extends MiddlewareAware
{
    /**
     * Get the domain defined for the route.
     *
     * @return null|string
     */
    public function getDomain(): ?string;

    /**
     * Get the URI that the route responds to.
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get the name of the route instance.
     *
     * @return null|string
     */
    public function getName(): ?string;

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
     * @param null|string  $expression
     *
     * @return $this
     */
    public function where($name, ?string $expression = null): Route;

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware(): array;

    /**
     * Return all disabled middlewares.
     *
     * @return array
     */
    public function gatherDisabledMiddlewares(): array;

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
     * Get route identifier.
     *
     * @return string
     */
    public function getIdentifier(): string;

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
     * Add a suffix to the route URI.
     *
     * @param string $suffix
     *
     * @return $this
     */
    public function addSuffix(string $suffix): Route;

    /**
     * Get the suffix of the route instance.
     *
     * @return null|string
     */
    public function getSuffix(): ?string;

    /**
     * Set a parameter to the given value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addParameter($name, $value): Route;

    /**
     * Get a given parameter from the route.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return object|string
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
     * Unset a parameter on the route if it is set.
     *
     * @param string $name
     *
     * @return void
     */
    public function forgetParameter(string $name): void;

    /**
     * The regular expression requirements.
     *
     * @return array
     */
    public function getSegments(): array;

    /**
     * Get the controller instance for the route.
     *
     * @return mixed
     */
    public function getController();

    /**
     * Run the route action and return the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run(ServerRequestInterface $serverRequest): ResponseInterface;
}
