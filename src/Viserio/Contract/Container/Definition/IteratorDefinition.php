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

interface IteratorDefinition extends Definition
{
    /**
     * Returns the argument.
     *
     * @return array|null
     */
    public function getArgument(): ?array;

    /**
     * Set a array argument.
     *
     * @param array<int|string, mixed> $argument
     *
     * @return $this
     */
    public function setArgument(array $argument): self;
}
