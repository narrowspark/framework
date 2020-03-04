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
     * @return static
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
