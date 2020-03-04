<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Route extends MiddlewareAware
{
    /**
     * Get the domain defined for the route.
     */
    public function getDomain(): ?string;

    /**
     * Get the URI that the route responds to.
     */
    public function getUri(): string;

    /**
     * Get the name of the route instance.
     */
    public function getName(): ?string;

    /**
     * Add or change the route name.
     */
    public function setName(string $name): self;

    /**
     * Get the HTTP verbs the route responds to.
     */
    public function getMethods(): array;

    /**
     * Set a regular expression requirement on the route.
     *
     * @param array|string $name
     */
    public function where($name, ?string $expression = null): self;

    /**
     * Get all middleware, including the ones from the controller.
     */
    public function gatherMiddleware(): array;

    /**
     * Return all disabled middleware.
     */
    public function gatherDisabledMiddleware(): array;

    /**
     * Determine if the route only responds to HTTP requests.
     */
    public function isHttpOnly(): bool;

    /**
     * Determine if the route only responds to HTTPS requests.
     */
    public function isHttpsOnly(): bool;

    /**
     * Get the action name for the route.
     */
    public function getActionName(): string;

    /**
     * Get the action array for the route.
     */
    public function getAction(): array;

    /**
     * Set the action array for the route.
     */
    public function setAction(array $action): self;

    /**
     * Get route identifier.
     */
    public function getIdentifier(): string;

    /**
     * Add a prefix to the route URI.
     */
    public function addPrefix(string $prefix): self;

    /**
     * Get the prefix of the route instance.
     */
    public function getPrefix(): string;

    /**
     * Add a suffix to the route URI.
     */
    public function addSuffix(string $suffix): self;

    /**
     * Get the suffix of the route instance.
     */
    public function getSuffix(): ?string;

    /**
     * Set a parameter to the given value.
     *
     * @param string $name
     */
    public function addParameter($name, $value): self;

    /**
     * Get a given parameter from the route.
     *
     * @return object|string
     */
    public function getParameter(string $name, $default = null);

    /**
     * Determine a given parameter exists from the route.
     */
    public function hasParameter(string $name): bool;

    /**
     * Get the key / value list of parameters for the route.
     */
    public function getParameters(): array;

    /**
     * Unset a parameter on the route if it is set.
     */
    public function removeParameter(string $name): void;

    /**
     * The regular expression requirements.
     */
    public function getSegments(): array;

    /**
     * Get the controller instance for the route.
     */
    public function getController();

    /**
     * Run the route action and return the response.
     */
    public function run(ServerRequestInterface $serverRequest): ResponseInterface;
}
