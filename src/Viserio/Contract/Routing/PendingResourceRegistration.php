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

interface PendingResourceRegistration extends MiddlewareAware
{
    /**
     * Set the methods the controller should apply to.
     *
     * @param string[] $methods
     */
    public function only(array $methods): self;

    /**
     * Set the methods the controller should exclude.
     *
     * @param string[] $methods
     */
    public function except(array $methods): self;

    /**
     * Set the route names for controller actions.
     *
     * @param string[] $names
     */
    public function addNames(array $names): self;

    /**
     * Set the route name for a controller action.
     */
    public function setName(string $method, string $name): self;

    /**
     * Override the route parameter names.
     */
    public function setParameters(array $parameters): self;

    /**
     * Override a route parameter's name.
     */
    public function setParameter(string $previous, string $new): self;
}
