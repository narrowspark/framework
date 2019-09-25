<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Routing;

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
     * @return self
     */
    public function setName(string $name): self;

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
     * @return self
     */
    public function where($name, ?string $expression = null): self;

    /**
     * Get all middleware, including the ones from the controller.
     *
     * @return array
     */
    public function gatherMiddleware(): array;

    /**
     * Return all disabled middleware.
     *
     * @return array
     */
    public function gatherDisabledMiddleware(): array;

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
     * @return self
     */
    public function setAction(array $action): self;

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
     * @return self
     */
    public function addPrefix(string $prefix): self;

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
     * @return self
     */
    public function addSuffix(string $suffix): self;

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
     * @return self
     */
    public function addParameter($name, $value): self;

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
    public function removeParameter(string $name): void;

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
