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

namespace Viserio\Contract\Container\Definition;

/**
 * @mixin \Viserio\Contract\Container\Definition\Definition
 */
interface ClosureDefinition extends ArgumentAwareDefinition, AutowiredAwareDefinition, DecoratorAwareDefinition, Definition, TagAwareDefinition
{
    /**
     * Set closure to be executed after call.
     *
     * @param bool $bool
     *
     * @return static
     */
    public function setExecutable(bool $bool);

    /**
     * Check if the closure is executable.
     *
     * @return bool
     */
    public function isExecutable(): bool;
}
