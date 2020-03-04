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

namespace Viserio\Contract\Container;

use Psr\Container\ContainerInterface;

interface DelegateAwareContainer
{
    /**
     * @return static
     */
    public function setDelegates(array $delegates);

    /**
     * Delegate a backup container to be checked for services if it
     * cannot be resolved via this container.
     *
     * @return static
     */
    public function delegate(ContainerInterface $container);

    /**
     * Returns true if service is registered in one of the delegated backup containers.
     */
    public function hasInDelegate(string $abstract): bool;
}
