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

namespace Viserio\Contract\Container;

use Psr\Container\ContainerInterface;

interface DelegateAwareContainer
{
    /**
     * @param array $delegates
     *
     * @return static
     */
    public function setDelegates(array $delegates);

    /**
     * Delegate a backup container to be checked for services if it
     * cannot be resolved via this container.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return static
     */
    public function delegate(ContainerInterface $container);

    /**
     * Returns true if service is registered in one of the delegated backup containers.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function hasInDelegate(string $abstract): bool;
}
