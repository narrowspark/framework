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

interface AutowiredAwareDefinition
{
    /**
     * Is the definition autowired?
     *
     * @return bool
     */
    public function isAutowired(): bool;

    /**
     * Enables/disables autowiring.
     *
     * @param bool $autowired
     *
     * @return static
     */
    public function setAutowired(bool $autowired);
}
