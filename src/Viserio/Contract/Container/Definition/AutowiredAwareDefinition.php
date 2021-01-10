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

namespace Viserio\Contract\Container\Definition;

interface AutowiredAwareDefinition
{
    /**
     * Is the definition autowired?
     */
    public function isAutowired(): bool;

    /**
     * Enables/disables autowiring.
     *
     * @return static
     */
    public function setAutowired(bool $autowired);
}
