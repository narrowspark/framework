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

namespace Viserio\Contract\Container\Traits;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{
    /**
     * Container instance.
     *
     * @var null|\Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return static
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
