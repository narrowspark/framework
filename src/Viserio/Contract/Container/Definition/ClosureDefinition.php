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

/**
 * @mixin \Viserio\Contract\Container\Definition\Definition
 */
interface ClosureDefinition extends ArgumentAwareDefinition, AutowiredAwareDefinition, DecoratorAwareDefinition, Definition, TagAwareDefinition
{
    /**
     * Set closure to be executed after call.
     *
     * @return static
     */
    public function setExecutable(bool $bool);

    /**
     * Check if the closure is executable.
     */
    public function isExecutable(): bool;
}
