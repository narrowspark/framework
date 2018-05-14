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

interface PendingResourceRegistration extends MiddlewareAware
{
    /**
     * Set the methods the controller should apply to.
     *
     * @param string[] $methods
     *
     * @return self
     */
    public function only(array $methods): self;

    /**
     * Set the methods the controller should exclude.
     *
     * @param string[] $methods
     *
     * @return self
     */
    public function except(array $methods): self;

    /**
     * Set the route names for controller actions.
     *
     * @param string[] $names
     *
     * @return self
     */
    public function addNames(array $names): self;

    /**
     * Set the route name for a controller action.
     *
     * @param string $method
     * @param string $name
     *
     * @return self
     */
    public function setName(string $method, string $name): self;

    /**
     * Override the route parameter names.
     *
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self;

    /**
     * Override a route parameter's name.
     *
     * @param string $previous
     * @param string $new
     *
     * @return self
     */
    public function setParameter(string $previous, string $new): self;
}
